<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khôi phục mật khẩu</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

    * {
      margin: 0;
      padding: 0;
      font-family: 'Roboto', sans-serif;
    }

    *::before,
    *::after {
      box-sizing: border-box;
    }
  </style>
</head>

<body>
  <div style="background:#f2f2f2; padding:20px 0;">
    <div style="max-width:680px;margin:auto;">
      <div style="background-color: #5b21b6; padding:20px 25px; display:flex; align-items:center; color:#fff;">
        <img src="https://dangkhoashop.netlify.app/assets/images/logo.png" alt=""
          style="height:50px; border-radius: 12px;">
        <b style="font-size:18px; margin-left:20px; line-height:50px;">DangKhoa Shop</b>
      </div>

      <div style="background:white; padding:30px 20px; line-height: 30px;">
        <h4 style="font-size:16px; font-weight:600; margin-bottom:19px;">Xin chào <strong>{{ $name }}</strong>,</h4>
        <p>Bạn đã yêu cầu khôi phục mật khẩu cho tài khoản:</p>
        <p><strong>{{ $getEmail }}</strong></p>

        <p>Mã xác nhận của bạn là:</p>
        <p style="font-size:20px; font-weight:700; color:#5b21b6;">{{ $code }}</p>

        <p>Mã sẽ hết hạn sau 5 phút.</p>
        <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>

        <p>Trân trọng,</p>
        <p><strong>Đội ngũ DangKhoa Shop</strong></p>
      </div>
    </div>
  </div>
</body>

</html>
