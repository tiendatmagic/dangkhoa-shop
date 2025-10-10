<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đơn hàng mới</title>
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

    .email>a {
      text-decoration: none;
      color: #000;
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
        <h4 style="font-size:16px; font-weight:600; margin-bottom:19px;">Xin chào <strong>Admin</strong>,</h4>
        <p>Hệ thống vừa có đơn đặt hàng mới:</p>

        <p>Đơn hàng mã: <strong style="color: #5b21b6;">{{ $order_code }}</strong></p>
        <p>Tên khách hàng: {{ $request->data['name'] }}</p>
        <p>Email: <span class="email">{{ $request->data['email'] }}</span></p>
        <p>Số điện thoại: <span class="phone">{{ $request->data['phone'] }}</span></p>
        <p>Địa chỉ: {{ $request->data['address'] }}</p>
        <p>Tổng đơn hàng: <strong>$ {{ number_format($total, 4) }}</strong></p>
        <p>Ghi chú: {{ $request->data['note'] ?? '-' }}</p>

        <p>Trân trọng,</p>
        <p><strong>Đội ngũ phát triển</strong></p>
      </div>
    </div>
  </div>
</body>

</html>