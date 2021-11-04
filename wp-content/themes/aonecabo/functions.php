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
    $message_t = $request['_trip_type'] == 'o' ? 'ARRIVAL' : 'ROUND-TRIP';
    $voucher   = "A-".mt_rand();

    if ($request['_trip_type'] == 'o')
    {
        if ($request['_location_start'] == 0)
            $message_t = "ARRIVAL";
        if ($request['_location_end'] == 0)
            $message_t = "DEPARTURE";
    } else {
        $message_t = 'ROUND TRIP';
    }

    //fetch resort name
    $query  = "SELECT * FROM resorts WHERE id = $resort_id";
    $resort = $wpdb->get_row($query);

    //fetch unit name
    $query  = "SELECT * FROM units WHERE id = {$request['_unit']}";
    $unit   = $wpdb->get_row($query);

    $location_start = $resort->name;

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
           "location_start"     => $request['_location_start'],
           "location_end"       => $request['_location_end'],
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
    $mail->AddCC('angel@aonecabodeluxetransportation.com', 'Angel');
    $mail->AddCC('miguel@aonecabodeluxetransportation.com', 'Miguel');
    $mail->AddCC('reservations@aonecabodeluxetransportation.com', 'Reservations');
    $mail->AddCC('aonecabo@gmail.com', 'A One Cabo');
    $mail->addBCC('fdo.valderrabano@gmail.com', 'Fernando Valderrabano');

    $mail->isHTML(true);

    if ($message_t == "ARRIVAL") {
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
                <table width="600" align="center" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block; padding-top:30px; margin-bottom: 50px; color: #005899;">
                    <tr>
                        <td colspan="2" style="padding: 10px; background: #ffffff; text-align: center">
                            <img src="http://www.aonecabodeluxetransportation.com/wp-content/uploads/2017/11/cropped-logo.png" alt="" style="width: 100px;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; border: 0; margin: 0 auto; display: block; width: 100%;">
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px; font-size: 14px;"><strong>PRIVATE SERVICE</strong></td>
                                    <td colspan="3" style="width: 450px; border: 1px solid #005899;  padding: 5px; font-size: 16px; text-align: center;">
                                        <strong>'.$message_t.' TRANSFER VOUCHER '.$voucher.'</strong>
                                    </td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Name</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$fullname.'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Arrival Date</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                        date('m/d/Y', strtotime($request['_arrival_date'])). " ".
                                        date('h:i a', strtotime($request['_arrival_time'])).'
                                    </td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Email</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_contact_email'].'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Phone number</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_contact_phone'].'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Hotel</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$location_start.'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Passengers</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_passengers'].'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Arrival Flight</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                        $request['_arrival_company']." ".$request['_arrival_flight'].
                                    '</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Vehicle</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$unit->name.'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Total</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">$ '.$request['_total'].' usd</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Pay method</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.ucfirst($request['pay_method']).'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Comments</td>
                                    <td colspan="3" style="width: 450px; border: 1px solid #005899; padding: 5px;">'.
                                        $request['_contact_request'].
                                    '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <br><br>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Dear client Once you have collected your bags and cleared customs, please proceed to exit sliding doors. It is very important to note that you must proceed all the way outside without stopping inside the terminal.</p>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Sales representatives within the terminal are extremely creative and persistent in their techniques and should be avoided if you are not interested in purchasing their products and services.
                            </p>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Outside the terminal, waiting staff will be standing canopy number 3 with a sign with <span style="color: #ec7728; font-weight: bold;">YOUR NAME</span> on it to provide proper meet and greet service, and to direct you to your transfer vehicle.</p>
                            <p style="text-align: center; color: #005899; font-size: 16px;">
                                <strong>For any changes, please advise at least 24 hours before at  52 1 (624) 229 38 91 or email us at aonecabo@gmail.com</strong>
                            </p>
                            <br><br>
                        </td>
                    </tr>';
    }

    if ($message_t == "DEPARTURE") {
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
                <table width="600" align="center" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block; padding-top:30px; margin-bottom: 50px; color: #005899;">
                    <tr>
                        <td colspan="2" style="padding: 10px; background: #ffffff; text-align: center">
                            <img src="http://www.aonecabodeluxetransportation.com/wp-content/uploads/2017/11/cropped-logo.png" alt="" style="width: 100px;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; border: 0; margin: 0 auto; display: block; width: 100%;">
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px; font-size: 14px;"><strong>PRIVATE SERVICE</strong></td>
                                    <td colspan="3" style="width: 450px; border: 1px solid #005899;  padding: 5px; font-size: 16px; text-align: center;">
                                        <strong>'.$message_t.' TRANSFER VOUCHER '.$voucher.'</strong>
                                    </td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Name</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$fullname.'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Departure Date</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                        date('m/d/Y', strtotime($request['_arrival_date'])). " ".
                                        date('h:i a', strtotime($request['_arrival_time'])).'
                                    </td>
                                </tr>';

        // hide contact
        // $html =                '<tr style="border: 1px solid #005899">
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Email</td>
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_contact_email'].'</td>
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Phone number</td>
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_contact_phone'].'</td>
        //                         </tr>';

        $html .=                '<tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Meeting at</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$location_start.'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Passengers</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_passengers'].'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Arrival Flight</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                        $request['_arrival_company']." ".$request['_arrival_flight'].
                                    '</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Vehicle</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$unit->name.'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Total</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">$ '.$request['_total'].' usd</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Pay method</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.ucfirst($request['pay_method']).'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Comments</td>
                                    <td colspan="3" style="width: 450px; border: 1px solid #005899; padding: 5px;">'.
                                        $request['_contact_request'].
                                    '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
        ';

        if ($request['pay_method'] == 'cash') {
            $html .=    '<tr>
                            <td colspan="2">
                                <br><br>
                                <p style="text-align: center; color: #005899; font-size: 18px;"><strong>PLEASE PAY TO YOUR DRIVER $'.$request['_total'].' USD FOR <br> YOUR PRIVATE RETURN TRANSFER</strong><br>
                                    <span style="color: #ec7728; font-weight: bold;">(Gratuity is not included)</span>
                                </p>
                            </td>
                        </tr>';
        }

        $html .=    '<tr>
                        <td colspan="2">
                            <br><br>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Note: Please be ready at the main lobby 5 minutes before your pick-up time. </p><p style="text-align: center; color: #005899; font-size: 16px;"> For any changes, please call A ONE CABO at 52 1 (624) 229 38 91 or email us at aonecabo@gmail.com  (12 hours prior to your flight). </p>
                        </td>
                    </tr>';
    } // departure

    if ($message_t == 'ROUND TRIP') :
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
                <table width="600" align="center" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: 0; border: 0; margin: 0 auto; display: block; padding-top:30px; margin-bottom: 50px; color: #005899;">
                    <tr>
                        <td colspan="2" style="padding: 10px; background: #ffffff; text-align: center">
                            <img src="http://www.aonecabodeluxetransportation.com/wp-content/uploads/2017/11/cropped-logo.png" alt="" style="width: 100px;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; border: 0; margin: 0 auto; display: block; width: 100%;">
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px; font-size: 14px;"><strong>PRIVATE SERVICE</strong></td>
                                    <td colspan="3" style="width: 450px; border: 1px solid #005899;  padding: 5px; font-size: 16px; text-align: center;">
                                        <strong>'.$message_t.' TRANSFER VOUCHER '.$voucher.'</strong>
                                    </td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Name</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$fullname.'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Arrival Date</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                        date('m/d/Y', strtotime($request['_arrival_date'])). " ".
                                        date('h:i a', strtotime($request['_arrival_time'])).'
                                    </td>
                                </tr>';

        // $html =                '<tr style="border: 1px solid #005899">
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Email</td>
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_contact_email'].'</td>
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Phone number</td>
        //                             <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_contact_phone'].'</td>
        //                         </tr>';

        $html .=                '<tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Hotel</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$location_start.'</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Passengers</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_passengers'].'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Arrival Flight</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                        $request['_arrival_company']." ".$request['_arrival_flight'].
                                    '</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Vehicle</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$unit->name.'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Total</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">$ '.$request['_total'].' usd</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Pay method</td>
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.ucfirst($request['pay_method']).'</td>
                                </tr>
                                <tr style="border: 1px solid #005899">
                                    <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Comments</td>
                                    <td colspan="3" style="width: 450px; border: 1px solid #005899; padding: 5px;">'.
                                        $request['_contact_request'].
                                    '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <br><br>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Dear client Once you have collected your bags and cleared customs, please proceed to exit sliding doors. It is very important to note that you must proceed all the way outside without stopping inside the terminal.</p>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Sales representatives within the terminal are extremely creative and persistent in their techniques and should be avoided if you are not interested in purchasing their products and services.
                            </p>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Outside the terminal, waiting staff will be standing canopy number 3 with a sign with <span style="color: #ec7728; font-weight: bold;">YOUR NAME</span> on it to provide proper meet and greet service, and to direct you to your transfer vehicle.</p>
                            <p style="text-align: center; color: #005899; font-size: 16px;">
                                <strong>For any changes, please advise at least 24 hours before at 52 1 (624) 229 38 91 or email us at aonecabo@gmail.com</strong>
                            </p>
                            <br><br>
                        </td>
                    </tr>';

        $html .= '<tr>
                    <td colspan="2">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; border: 0; margin: 0 auto; display: block; width: 100%;">
                            <tr style="border: 1px solid #005899">
                                <td colspan="2" style="width: 299px; border: 1px solid #005899; padding: 5px; font-size: 16px;">
                                    <strong>DEPARTURE NOTICE</strong>
                                </td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Flight Time</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                    date('h:i a', strtotime($request['_departure_time'])).'
                                </td>
                            </tr>
                            <tr style="border: 1px solid #005899">
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Departure Flight</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_departure_company'].' '.$request['_departure_flight'].'</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Passengers</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$request['_passengers'].'</td>
                            </tr>
                            <tr style="border: 1px solid #005899">
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Hotel</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.$location_start.'</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">Departure Date</td>
                                <td style="width: 149px; border: 1px solid #005899; padding: 5px;">'.
                                    date('m/d/Y', strtotime($request['_departure_date'])).'
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>';

        $html .=    '<tr>
                        <td colspan="2">
                            <br><br>
                            <p style="text-align: center; color: #005899; font-size: 16px;">Note: Please be ready at the main lobby 5 minutes before your pick-up time. </p><p style="text-align: center; color: #005899; font-size: 16px;"> For any changes, please call A ONE CABO at (52) 624 3552938 or email us at aonecabo@gmail.com  (12 hours prior to your flight). </p>
                        </td>
                    </tr>';
    endif;

    $html .=  '<tr>
                    <td colspan="2" style="background: #ffffff; color: #a4a7ac; padding: 30px; text-align: center;">
                        <p>&copy;2019 A One Cabo Deluxe Transportation. All Rights Reserved.</p>
                        <p>Office +52 1 (624) 355 29 38 - Mobile +52 1 (624) 229 38 91</p>
                    </td>
                </tr>
            </table>
            </body>
            </html>';

    $mail->Subject = $message_t.' TRANSFER VOUCHER '.$voucher;
    $mail->Body    = $html;

    if (!$mail->send()) die ('There was an error communicating with the server.');

    if ($request['pay_method'] == 'paypal')
    {
        $enableSandbox = PAYPAL_SANDBOX;

        // PayPal settings. Change these to your account details and the relevant URLs
        // for your site.
        $paypalConfig = [
            'email'      =>  PAYPAL_EMAIL,
            'return_url' => 'http://www.aonecabodeluxetransportation.com/payment-successful',
            'cancel_url' => 'http://www.aonecabodeluxetransportation.com/payment-cancelled',
            'notify_url' => 'http://www.aonecabodeluxetransportation.com/paypal-notify'
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
        $data['currency_code']  = 'USD';
        $data['amount']         = $request['_total'];
        $data['item_name']      = $voucher;

        $queryString = http_build_query($data);
        $queryString = urldecode($queryString);
        $redirectURL = $paypalUrl . '?' . $queryString;
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