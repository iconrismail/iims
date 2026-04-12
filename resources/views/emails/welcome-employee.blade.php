<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:30px">
<div style="max-width:500px;margin:0 auto;background:white;border-radius:8px;padding:30px">
  <h2 style="color:#1a1a2e">Welcome to IIMS!</h2>
  <p>Dear {{ $user->name }},</p>
  <p>Your employee account has been created. Click the button below to set your password and access the system.</p>
  <div style="text-align:center;margin:25px 0">
    <a href="{{ $resetUrl }}" style="background:#1a1a2e;color:white;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:15px;display:inline-block">
      Set Your Password
    </a>
  </div>
  <p style="color:#6c757d;font-size:13px">This link expires in 60 minutes. If you did not expect this email, please contact your administrator.</p>
  <hr style="border:none;border-top:1px solid #eee;margin:20px 0">
  <p style="color:#6c757d;font-size:12px">Login email: <strong>{{ $user->email }}</strong></p>
</div></body></html>
