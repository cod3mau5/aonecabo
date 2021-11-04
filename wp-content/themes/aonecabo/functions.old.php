<?php

function understrap_remove_scripts() {
    wp_dequeue_style( 'understrap-styles' );
    wp_deregister_style( 'understrap-styles' );

    wp_dequeue_script( 'understrap-scripts' );
    wp_deregister_script( 'understrap-scripts' );
    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}

function theme_enqueue_styles() {
	// Get the theme data
	$the_theme = wp_get_theme();

    wp_enqueue_style( 'bootstrap-datetimepicker', get_stylesheet_directory_uri() . '/css/bootstrap-datetimepicker.min.css', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'popper-scripts', get_template_directory_uri() . '/js/popper.min.js', array(), false);
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
    wp_enqueue_script( 'momentjs', get_stylesheet_directory_uri() . '/js/moment.min.js', array(), $the_theme->get( 'Version' ), true );
    wp_enqueue_script( 'bootstrap-datetimepicker', get_stylesheet_directory_uri() . '/js/bootstrap-datetimepicker.min.js', array(), $the_theme->get( 'Version' ), true );
    wp_enqueue_script( 'jquery-validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js', array(), $the_theme->get( 'Version' ), true );
    wp_enqueue_script( 'jquery-validate-methods', get_stylesheet_directory_uri() . '/js/additional-methods.min.js', array(), $the_theme->get( 'Version' ), true );
}

function home_booking_form() {
    include dirname(__FILE__) . "/partials/home_booking.php";
}

function rates_collapse() {

    global $wpdb;

    $query = "SELECT * FROM units WHERE deleted_at IS NULL";
    $units = $wpdb->get_results($query);

    $query = "SELECT * FROM rates WHERE deleted_at IS NULL";
    $rates = $wpdb->get_results($query);

    foreach ($rates as $price) {
        $prices[$price->unit_id][$price->zone_id] = ['oneway'=>$price->oneway, 'roundtrip'=>$price->roundtrip];
    }

    include dirname(__FILE__) . '/partials/rates_collapse.php';
}

function reserve_form() {
    global $wp;

    if (!empty($_POST)) insertReservation($_POST);

    $action_page = home_url( $wp->request );

    include dirname(__FILE__) . '/partials/reserve_form.php';
}

function register_footer_menu() {
    register_nav_menu( 'footer-menu', __('Footer menu') );
}

function insertReservation($request) {

    include dirname(__FILE__) . '/vendor/autoload.php';
    include dirname(__FILE__) . '/config.php';

    global $wpdb;

    $resort_id = $request['_location_start'] > 0 ? $request['_location_start'] : $request['_location_end'];
    $trip_type = $request['_trip_type'] == 'o' ? 'oneway' : 'roundtrip';
    $voucher   = "A-".mt_rand();

    //fetch resort name
    $query  = "SELECT * FROM resorts WHERE id = $resort_id";
    $resort = $wpdb->get_row($query);

    //fetch unit name
    $query  = "SELECT * FROM units WHERE id = {$request['_unit']}";
    $unit   = $wpdb->get_row($query);

    if ($request['_location_start'] > 0) {
        $location_start = $resort->name;
        $location_ends  = "Los Cabos Int. Airport";
    } else {
        $location_start = "Los Cabos Int. Airport";
        $location_ends  = $resort->name;
    }

    if (!empty($request['_arrival_date']))
        $request['_arrival_date'] = date('Y-m-d', strtotime($request['_arrival_date']));

    if (!empty($request['_departure_date'])) {
        $request['_departure_date'] = date('Y-m-d', strtotime($request['_departure_date']));
    } else {
        $request['_departure_date'] = null;
    }

    if (!empty($request['_arrival_time']))
        $request['_arrival_time'] = date('H:i:s', strtotime($request['_arrival_time']));

    if (!empty($request['_departure_time'])) {
        $request['_departure_time'] = date('H:i:s', strtotime($request['_departure_time']));
    } else {
        $request['_departure_time'] = null;
    }

    $fullname = $request['_contact_firstname'] . " " . $request['_contact_lastname'];

    $wpdb->insert("reservations", array(
           "resort_id"          => $resort_id,
           "unit_id"            => $request['_unit'],
           "voucher"            => $voucher,
           "fullname"           => $fullname,
           "email"              => $request['_contact_email'],
           "type"               => $trip_type,
           "phone"              => $request['_contact_phone'],
           "passengers"         => $request['_passengers'],
           "arrival_date"       => $request['_arrival_date'],
           "arrival_time"       => $request['_arrival_time'],
           "arrival_airline"    => $request['_arrival_company'],
           "arrival_flight"     => $request['_arrival_flight'],
           "departure_date"     => $request['_departure_date'],
           "departure_time"     => $request['_departure_time'],
           "departure_airline"  => $request['_departure_company'],
           "departure_flight"   => $request['_departure_flight'],
           "comments"           => $request['_contact_request'],
           "payment_type"       => $request['pay_method'],
           "subtotal"           => !empty($request['_subtotal']) ? $request['_subtotal'] : 0,
           "total"              => !empty($request['_total']) ? $request['_total'] : 0,
           "source"             => 'web',
           "created_at"         => date('Y-m-d H:m:i'),
           "updated_at"         => date('Y-m-d H:m:i')
    ));

    $mail = new PHPMailer;

    if (MAIL_DEBUG) {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';
    }

    if (MAIL_HOST == 'smtp.ionos.mx') {
        $mail->IsSendmail();
    } else {
        $mail->isSMTP();
    }

    $mail->Host         = MAIL_HOST;
    $mail->SMTPSecure   = 'tls';
    $mail->SMTPAuth     = MAIL_AUTH;
    $mail->Username     = MAIL_USER;
    $mail->Password     = MAIL_PASS;
    $mail->Port         = MAIL_PORT;

    $mail->setFrom(MAIL_USER, MAIL_FROM);
    $mail->addReplyTo(MAIL_USER, MAIL_FROM);
    $mail->addAddress( $request['_contact_email'] , $fullname);
    $mail->addBCC(MAIL_DEST, MAIL_DEST_NAME);

    $mail->isHTML(true);

    $html = '<!DOCTYPE html>
            <html>
            <head>
                <title></title>
                <style type="text/css">
                    @media  only screen and (max-width: 500px) {
                        table {
                            width: 100%;
                        }

                       .fulltd {
                            display: block;
                            width: 100%;
                        }
                        .logo {
                            text-align: center;
                        }
                    }
                </style>
            </head>
            <body>
            <table width="600" align="center" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block; padding-top:30px; margin-bottom: 50px;">
                <tr>
                    <td colspan="2" style="padding: 10px; background: #e4eef1; text-align: center">
                        <img src="http://www.aonecabo.com/wp-content/uploads/2017/11/cropped-logo.png" alt="" style="width: 100px;">
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding:5px 15px; font-family: Arial, Helvetica, sans-serif;">
                        <br><br>
                        <p style="font-size: 16px;"><strong>Thank you for your Request:</strong></p>
                        <p>This email is to confirm that your Reservation Request was received.</p>
                        <p>This is a short version of your request:</p>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding: 15px;">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block;">
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Your name:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$fullname.'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Your email:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_contact_email'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Your message:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_contact_request'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Passengers:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd"><strong>'.$request['_passengers'].'</strong></td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Unit:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd"><strong>'.$unit->name.'</strong></td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Subtotal:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd"><strong>$ '.$request['_subtotal'].' USD</strong></td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Total Cost:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd"><strong>$ '.$request['_total'].' USD</strong></td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Payment method:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd"><strong>'.ucfirst($request['pay_method']).'</strong></td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Voucher:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd"><strong>'.$voucher.'</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding:5px 15px; font-family: Arial, Helvetica, sans-serif;">
                        <p style="font-size: 16px;"><strong>Arrival Details</strong></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 15px;">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block;">
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Destination:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$location_start.'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Date:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_arrival_date'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Time:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_arrival_time'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Airline:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_arrival_company'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Flight:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_arrival_flight'].'</td>
                            </tr>
                        </table>
                    </td>
                </tr>';

    if ($trip_type == 'roundtrip') :

        $html .= '<tr>
                    <td colspan="2" style="padding:5px 15px; font-family: Arial, Helvetica, sans-serif;">
                        <p style="font-size: 16px;"><strong>Departure Details</strong></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 15px;">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block;">
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Destination:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$location_ends.'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Date:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_departure_date'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Time:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_departure_time'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Airline:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_departure_company'].'</td>
                            </tr>
                            <tr style="background-color: #FFFFFF;">
                                <td width="250" bgcolor="#FFFFFF" style="width: 200px; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">Arrival Flight:</td>
                                <td width="350" bgcolor="#FFFFFF" style="width: 400px; border-left: 5px solid #ffffff; padding:5px 15px;font-family: Arial, Helvetica, sans-serif;" class="fulltd">'.$request['_departure_flight'].'</td>
                            </tr>
                        </table>
                    </td>
                </tr>';
    endif;

    $html .=  '<tr>
                    <td colspan="2" style="background: #e4eef1; color: #a4a7ac; padding: 30px; text-align: center;">
                        <p>&copy;2019 A One Cabo Deluxe Transportation. All Right Reserved.</p>
                        <p>Office +52 1 (624) 355 29 38 - Mobile +52 1 (624) 229 38 91</p>
                    </td>
                </tr>
            </table>
            </body>
            </html>';

    $mail->Subject = 'Booking Voucher '. $voucher;
    $mail->Body    = $html;

    if (!$mail->send()) die ();

    if ($request['pay_method'] == 'paypal')
    {
        $enableSandbox = PAYPAL_SANDBOX;

        // PayPal settings. Change these to your account details and the relevant URLs
        // for your site.
        $paypalConfig = [
            'email'      =>  PAYPAL_EMAIL,
            'return_url' => 'http://www.aonecabo.com/payment-successful',
            'cancel_url' => 'http://www.aonecabo.com/payment-cancelled',
            'notify_url' => 'http://www.aonecabo.com/paypal-notify'
        ];

        $paypalUrl = $enableSandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

        foreach ($_POST['Paypal'] as $key => $value) {
            $data[$key] = stripslashes($value);
        }

        // Set the PayPal account.
        $data['business'] = $paypalConfig['email'];

        // Set the PayPal return addresses.
        $data['return'] = stripslashes($paypalConfig['return_url']);
        $data['cancel_return'] = stripslashes($paypalConfig['cancel_url']);
        $data['notify_url'] = stripslashes($paypalConfig['notify_url']);

        // Set the details about the product being purchased, including the amount
        // and currency so that these aren't overridden by the form data.
        $data['item_name']      = $voucher;
        $data['amount']         = $request['_total'];
        $data['currency_code']  = 'USD';

        $queryString = http_build_query($data);
        $redirectURL = $paypalUrl . '?' . $queryString;
        // die( $redirectURL);
        //header('location:' . $paypalUrl . '?' . $queryString);

        // wp_redirect('location:' . $paypalUrl . '?' . $queryString);
        echo "<script>window.location = '$redirectURL';</script>";
        // die ($paypalUrl . '?' . $queryString);
    }
}

add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
add_action( 'init', 'register_footer_menu' );

add_shortcode( 'booking_form',   'home_booking_form' );
add_shortcode( 'rates_collapse', 'rates_collapse' );
add_shortcode( 'reserve_form',   'reserve_form' );

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );