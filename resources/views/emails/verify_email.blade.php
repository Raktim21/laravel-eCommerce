{{-- <x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> --}}


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" style="font-family:arial, 'helvetica neue', helvetica, sans-serif">

    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <meta name="x-apple-disable-message-reformatting">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="telephone=no" name="format-detection">
        <title>Email Verification</title>

        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

        <style type="text/css">
            #outlook a {
            padding: 0;
            }

            .es-button {
            mso-style-priority: 100 !important;
            text-decoration: none !important;
            }

            a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
            }

            .es-desk-hidden {
            display: none;
            float: left;
            overflow: hidden;
            width: 0;
            max-height: 0;
            line-height: 0;
            mso-hide: all;
            }

            [data-ogsb] .es-button {
            border-width: 0 !important;
            padding: 15px 35px 15px 35px !important;
            }

            @media only screen and (max-width:600px) {

            p,
            ul li,
            ol li,
            a {
                line-height: 150% !important
            }

            h1,
            h2,
            h3,
            h1 a,
            h2 a,
            h3 a {
                line-height: 120%
            }

            h1 {
                font-size: 30px !important;
                text-align: center !important
            }

            h2 {
                font-size: 24px !important;
                text-align: center !important
            }

            h3 {
                font-size: 20px !important;
                text-align: center !important
            }

            .es-header-body h1 a,
            .es-content-body h1 a,
            .es-footer-body h1 a {
                font-size: 30px !important;
                text-align: center !important
            }

            .es-header-body h2 a,
            .es-content-body h2 a,
            .es-footer-body h2 a {
                font-size: 24px !important;
                text-align: center !important
            }

            .es-header-body h3 a,
            .es-content-body h3 a,
            .es-footer-body h3 a {
                font-size: 20px !important;
                text-align: center !important
            }

            .es-menu td a {
                font-size: 14px !important
            }

            .es-header-body p,
            .es-header-body ul li,
            .es-header-body ol li,
            .es-header-body a {
                font-size: 14px !important
            }

            .es-content-body p,
            .es-content-body ul li,
            .es-content-body ol li,
            .es-content-body a {
                font-size: 14px !important
            }

            .es-footer-body p,
            .es-footer-body ul li,
            .es-footer-body ol li,
            .es-footer-body a {
                font-size: 14px !important
            }

            .es-infoblock p,
            .es-infoblock ul li,
            .es-infoblock ol li,
            .es-infoblock a {
                font-size: 12px !important
            }

            *[class="gmail-fix"] {
                display: none !important
            }

            .es-m-txt-c,
            .es-m-txt-c h1,
            .es-m-txt-c h2,
            .es-m-txt-c h3 {
                text-align: center !important
            }

            .es-m-txt-r,
            .es-m-txt-r h1,
            .es-m-txt-r h2,
            .es-m-txt-r h3 {
                text-align: right !important
            }

            .es-m-txt-l,
            .es-m-txt-l h1,
            .es-m-txt-l h2,
            .es-m-txt-l h3 {
                text-align: left !important
            }

            .es-m-txt-r img,
            .es-m-txt-c img,
            .es-m-txt-l img {
                display: inline !important
            }

            .es-button-border {
                display: inline-block !important
            }

            a.es-button,
            button.es-button {
                font-size: 18px !important;
                display: inline-block !important
            }

            .es-adaptive table,
            .es-left,
            .es-right {
                width: 100% !important
            }

            .es-content table,
            .es-header table,
            .es-footer table,
            .es-content,
            .es-footer,
            .es-header {
                width: 100% !important;
                max-width: 600px !important
            }

            .es-adapt-td {
                display: block !important;
                width: 100% !important
            }

            .adapt-img {
                width: 100% !important;
                height: auto !important
            }

            .es-m-p0 {
                padding: 0px !important
            }

            .es-m-p0r {
                padding-right: 0px !important
            }

            .es-m-p0l {
                padding-left: 0px !important
            }

            .es-m-p0t {
                padding-top: 0px !important
            }

            .es-m-p0b {
                padding-bottom: 0 !important
            }

            .es-m-p20b {
                padding-bottom: 20px !important
            }

            .es-mobile-hidden,
            .es-hidden {
                display: none !important
            }

            tr.es-desk-hidden,
            td.es-desk-hidden,
            table.es-desk-hidden {
                width: auto !important;
                overflow: visible !important;
                float: none !important;
                max-height: inherit !important;
                line-height: inherit !important
            }

            tr.es-desk-hidden {
                display: table-row !important
            }

            table.es-desk-hidden {
                display: table !important
            }

            td.es-desk-menu-hidden {
                display: table-cell !important
            }

            .es-menu td {
                width: 1% !important
            }

            table.es-table-not-adapt,
            .esd-block-html table {
                width: auto !important
            }

            table.es-social {
                display: inline-block !important
            }

            table.es-social td {
                display: inline-block !important
            }

            .es-desk-hidden {
                display: table-row !important;
                width: auto !important;
                overflow: visible !important;
                max-height: inherit !important
            }
            }
        </style>
    </head>

    <body style="width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
        <div class="es-wrapper-color" style="background-color:#FFFFFF">
            <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#FFFFFF">
                <tr>
                    <td valign="top" style="padding:0;Margin:0">
                        <table cellpadding="0" cellspacing="0" class="es-header" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
                            <tr>
                                <td align="center" style="padding:0;Margin:0">
                                    <table bgcolor="#fad939" class="es-header-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:510px">
                                        <tr>
                                            <td align="left" style="padding:0;Margin:0;padding-left:20px;padding-right:20px">
                                                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr>
                                                        <td align="center" valign="top" style="padding:0;Margin:0;width:470px">
                                                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                <tr>
                                                                    <td align="center" height="40" style="padding:0;Margin:0"></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
                            <tr>
                                <td align="center" style="padding:0;Margin:0">
                                    <table class="es-content-body"style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:510px" cellspacing="0" cellpadding="0" align="center" bgcolor="#FAD939">
                                        <tr>
                                            <td align="left" style="padding:0;Margin:0">
                                                <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr>
                                                        <td class="es-m-p0r" valign="top" align="center" style="padding:0;Margin:0;width:510px">
                                                            <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                <tr>
                                                                    <td align="center" style="padding:0;Margin:0;position:relative">
                                                                        <img class="adapt-img" src="https://mefhdq.stripocdn.email/content/guids/bannerImgGuid/images/image16768006133445566.png" alt title width="510" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" height="324">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
                            <tr>
                                <td align="center" style="padding:0;Margin:0">
                                    <table bgcolor="#ffffff" class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FAD939;border-radius:0 0 50px 50px;width:510px">
                                        <tr>
                                            <td align="left" style="padding:0;Margin:0;padding-left:20px;padding-right:20px">
                                                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr>
                                                        <td align="center" valign="top" style="padding:0;Margin:0;width:470px">
                                                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                <tr>
                                                                    <td align="center" style="padding:0;Margin:0">
                                                                        <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:Poppins, sans-serif;font-size:38px;font-style:normal;font-weight:bold;color:#5d541d">
                                                                            Please confirm<br>your email address
                                                                        </h1>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" style="padding:0;Margin:0;padding-top:40px;padding-bottom:40px">
                                                                        <h3 style="Margin:0;line-height:24px;mso-line-height-rule:exactly;font-family:Poppins, sans-serif;font-size:20px;font-style:normal;font-weight:bold;color:#5D541D">
                                                                            Thanks for joining Selopia!
                                                                        </h3>
                                                                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:Poppins, sans-serif;line-height:27px;color:#5D541D;font-size:18px">
                                                                            <br>
                                                                        </p>
                                                                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:Poppins, sans-serif;line-height:27px;color:#5D541D;font-size:18px">
                                                                            To finish signing up, please confirm your email address. This ensures we have the
                                                                            right email in case we need to contact you.
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" style="padding:0;Margin:0">
                                                                        <span class="msohide es-button-border" style="border-style:solid;border-color:#2CB543;background:#8928c6;border-width:0px;display:inline-block;border-radius:30px;width:auto;mso-hide:all">
                                                                            <button type="button" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;border-style:solid;border-color:#8928c6;border-width:15px 35px 15px 35px;display:inline-block;background:#8928c6;border-radius:30px;font-family:Poppins, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center">
                                                                                {{$code}}
                                                                            </a>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                            style="Margin:0;padding-top:20px;padding-left:20px;padding-right:20px;padding-bottom:40px">
                                            <table cellpadding="0" cellspacing="0" width="100%"
                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                <tr>
                                                <td align="left" style="padding:0;Margin:0;width:470px">
                                                    <table cellpadding="0" cellspacing="0" width="100%" role="presentation"
                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr>
                                                        <td align="center" style="padding:0;Margin:0">
                                                        <p
                                                            style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:Poppins, sans-serif;line-height:21px;color:#5D541D;font-size:14px">
                                                            Thanks,<br>Selopia Team!&nbsp;</p>
                                                        </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0" class="es-header" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
                            <tr>
                                <td align="center" style="padding:0;Margin:0">
                                    <table bgcolor="#fad939" class="es-header-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:510px">
                                        <tr>
                                            <td align="left" style="padding:0;Margin:0;padding-left:20px;padding-right:20px">
                                                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr>
                                                        <td align="center" valign="top" style="padding:0;Margin:0;width:470px">
                                                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation"style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                <tr>
                                                                    <td align="center" height="40" style="padding:0;Margin:0"></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0" class="es-footer" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
                            <tr>
                                <td align="center" style="padding:0;Margin:0">
                                    <table bgcolor="#ffffff" class="es-footer-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#333333;border-radius:50px;width:510px">
                                        <tr>
                                            <td align="left" style="Margin:0;padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right:20px">
                                                <table cellpadding="0" cellspacing="0" class="es-left" align="left" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left">
                                                    <tr>
                                                        <td class="es-m-p20b" align="left" style="padding:0;Margin:0;width:225px">
                                                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                <tr>
                                                                    <td align="left" class="es-m-txt-c" style="padding:0;Margin:0;font-size:0px">
                                                                        <a target="_blank" href="https://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#FFFFFF;font-size:14px">
                                                                            <img src="https://mefhdq.stripocdn.email/content/guids/CABINET_217334892819859af13dcb72b8181a09e85f3568f3d86194a7ab7a9d8df6ada3/images/download_1_HMJ.png" alt="Logo" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" height="30" title="Logo" width="180">
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <table cellpadding="0" cellspacing="0" class="es-right" align="right" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:right">
                                                    <tr>
                                                        <td align="left" style="padding:0;Margin:0;width:225px">
                                                            <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                <tr>
                                                                    <td align="right" class="es-m-txt-c"style="padding:0;Margin:0;padding-top:5px;font-size:0">
                                                                        <table cellpadding="0" cellspacing="0" class="es-table-not-adapt es-social" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                            <tr>
                                                                                <td align="center" valign="top" style="padding:0;Margin:0;padding-right:10px">
                                                                                    <a target="_blank" href="https://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#FFFFFF;font-size:14px">
                                                                                        <img src="https://mefhdq.stripocdn.email/content/assets/img/social-icons/circle-white/facebook-circle-white.png" alt="Fb" title="Facebook" height="24" width="24" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                                                                                    </a>
                                                                                </td>
                                                                                <td align="center" valign="top" style="padding:0;Margin:0;padding-right:10px">
                                                                                    <a target="_blank" href="https://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#FFFFFF;font-size:14px">
                                                                                        <img src="https://mefhdq.stripocdn.email/content/assets/img/social-icons/circle-white/twitter-circle-white.png" alt="Tw" title="Twitter" height="24" width="24" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                                                                                    </a>
                                                                                </td>
                                                                                <td align="center" valign="top" style="padding:0;Margin:0;padding-right:10px">
                                                                                    <a target="_blank" href="https://viewstripo.email"style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#FFFFFF;font-size:14px">
                                                                                        <img src="https://mefhdq.stripocdn.email/content/assets/img/social-icons/circle-white/instagram-circle-white.png" alt="Ig" title="Instagram" height="24" width="24" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                                                                                    </a>
                                                                                </td>
                                                                                <td align="center" valign="top" style="padding:0;Margin:0">
                                                                                    <a target="_blank" href="https://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#FFFFFF;font-size:14px">
                                                                                        <img src="https://mefhdq.stripocdn.email/content/assets/img/social-icons/circle-white/youtube-circle-white.png" alt="Yt" title="Youtube" height="24" width="24" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>

</html>