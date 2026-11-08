<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackingAid Account</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f6f4; font-family: Arial, Helvetica, sans-serif; color: #26332d;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f6f4; padding: 40px 16px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 560px; background-color: #ffffff; border-radius: 12px; overflow: hidden;">

                    <tr>
                        <td style="background-color: #18332e; padding: 28px 36px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 700; letter-spacing: 0.5px;">
                                TrackingAid
                            </h1>
                            <p style="margin: 8px 0 0; color: #c9ded3; font-size: 13px;">
                                Disaster Logistics System
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 36px;">
                            <h2 style="margin: 0 0 18px; color: #18332e; font-size: 23px;">
                                Manage Your Account
                            </h2>

                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.7;">
                                Hello {{ $user->first_name ?: $user->name }},
                            </p>

                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.7;">
                                We received a request to set or reset the password for your TrackingAid account. Use the button below to continue.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 28px auto;">
                                <tr>
                                    <td align="center" style="background-color: #16803d; border-radius: 7px;">
                                        <a href="{{ $setupUrl }}"
                                           style="display: inline-block; padding: 14px 28px; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 700;">
                                            Continue to Account
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 12px; font-size: 13px; line-height: 1.6; color: #66736c;">
                                If the button doesn't work, copy and paste this link into your browser:
                            </p>

                            <p style="margin: 0 0 24px; font-size: 13px; line-height: 1.6; word-break: break-all;">
                                <a href="{{ $setupUrl }}" style="color: #16803d;">
                                    {{ $setupUrl }}
                                </a>
                            </p>

                            <div style="border-top: 1px solid #e5ebe7; padding-top: 20px;">
                                <p style="margin: 0 0 10px; font-size: 13px; line-height: 1.6; color: #66736c;">
                                    For your security, this link expires according to the system's password reset settings. Please do not share it with anyone.
                                </p>

                                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #66736c;">
                                    If you did not request this, you can safely ignore this email. Your account will remain unchanged unless the password is successfully updated.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f8faf9; padding: 20px 36px; text-align: center; border-top: 1px solid #e5ebe7;">
                            <p style="margin: 0; color: #84918a; font-size: 12px;">
                                &copy; {{ date('Y') }} TrackingAid. All rights reserved.
                            </p>
                            <p style="margin: 6px 0 0; color: #84918a; font-size: 12px;">
                                Disaster Logistics and Inventory Management
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
