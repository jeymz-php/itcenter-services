<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Temporary Password</title>
</head>
<body style="margin:0;padding:0;background:#f5f7f6;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7f6;padding:32px 16px">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(10,51,35,.08)">

          <tr>
            <td style="background:linear-gradient(135deg,#18633f,#249660);padding:22px 26px">
              <span style="color:#ffffff;font-size:16px;font-weight:800">UCC IT Center Services</span>
            </td>
          </tr>

          <tr>
            <td style="padding:26px">
              <p style="margin:0 0 6px;font-size:13px;color:#8aa89f;font-weight:700;text-transform:uppercase;letter-spacing:.05em">
                Password Reset by Administrator
              </p>
              <p style="margin:0 0 16px;font-size:15px;color:#1e3530">
                Hi {{ $recipientName }}, an IT Center administrator has reset your account password. Your new temporary password is below.
              </p>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2fbf7;border:1.5px solid #a8e8cc;border-radius:8px;margin-bottom:20px">
                <tr>
                  <td style="padding:16px;text-align:center">
                    <span style="font-family:'Courier New',monospace;font-size:20px;font-weight:800;color:#124530;letter-spacing:1px">{{ $temporaryPassword }}</span>
                  </td>
                </tr>
              </table>

              <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
                <tr>
                  <td style="border-radius:8px;background:linear-gradient(135deg,#18633f,#249660)">
                    <a href="{{ $loginUrl }}" target="_blank"
                       style="display:inline-block;padding:11px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none">
                      Log In Now
                    </a>
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-left:3px solid #f5a623;border-radius:8px">
                <tr>
                  <td style="padding:12px 16px;font-size:12px;color:#7a5c00;line-height:1.6">
                    For your security, please log in with this temporary password and change it to something only you know as soon as possible, from <strong>My Profile → Change Password</strong>.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:16px 26px;border-top:1px solid #f0f4f2">
              <p style="margin:0;font-size:11px;color:#8aa89f;line-height:1.6">
                This is an automated notification from the UCC IT Center Services System.
                If you believe this was done in error, please contact the IT Center immediately.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>