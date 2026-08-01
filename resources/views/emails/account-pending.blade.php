<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Registration Pending</title>
</head>
<body style="margin:0;padding:0;background:#f5f7f6;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7f6;padding:32px 16px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 5px 22px rgba(10,51,35,.09)">
<tr><td style="background:linear-gradient(135deg,#124530,#249660);padding:24px 28px;color:#fff">
  <div style="font-size:17px;font-weight:800">UCC IT Center Services</div>
  <div style="font-size:12px;color:rgba(255,255,255,.72);margin-top:4px">Account Registration Notification</div>
</td></tr>
<tr><td style="padding:28px">
  <div style="width:58px;height:58px;border-radius:16px;background:#fff3e0;color:#e67e00;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:18px">⌛</div>
  <p style="margin:0 0 8px;font-size:18px;font-weight:800;color:#1e3530">Your account is pending approval</p>
  <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4d6b61">Hi {{ $user->first_name }}, your IT Center Services account was created successfully. An administrator will verify your registration before service access is enabled.</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2fbf7;border:1px solid #a8e8cc;border-radius:10px;margin-bottom:20px">
    <tr><td style="padding:15px 17px;font-size:13px;line-height:1.7;color:#124530">
      <strong>ID Number:</strong> {{ $user->id_number }}<br>
      <strong>Campus:</strong> {{ config('campuses.'.$user->campus, $user->campus) }}<br>
      <strong>Account Type:</strong> {{ ucfirst(str_replace('_',' ',$user->user_type)) }}<br>
      <strong>Status:</strong> Pending Verification
    </td></tr>
  </table>

  <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:20px"><tr><td style="border-radius:8px;background:#1e7d4f">
    <a href="{{ $dashboardUrl }}" target="_blank" style="display:inline-block;padding:12px 22px;color:#fff;text-decoration:none;font-size:13px;font-weight:700">Check Account Status</a>
  </td></tr></table>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-left:3px solid #f5a623;border-radius:8px">
    <tr><td style="padding:13px 15px;font-size:12px;line-height:1.6;color:#7a5200">You will receive another email after an administrator approves your account. You may also sign in and use the <strong>Refresh Status</strong> button on the pending dashboard.</td></tr>
  </table>
</td></tr>
<tr><td style="padding:17px 28px;border-top:1px solid #f0f4f2;font-size:11px;line-height:1.6;color:#8aa89f">This is an automated email from the UCC IT Center Services System. Please contact the IT Center if you did not create this account.</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
