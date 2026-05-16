<?php

return [
    'username-required' => 'Tên người dùng hoặc email phải được cung cấp.',
    'password-required' => 'Vui lòng nhập mật khẩu tài khoản của bạn.',
    'email-required' => 'Một địa chỉ email hợp lệ phải được cung cấp để tiếp tục.',

    'login-title' => 'Đăng nhập để tiếp tục',

    'username-label' => 'Tên người dùng hoặc Email',
    'password-label' => 'Mật khẩu',

    'login-button' => 'Đăng nhập',
    'return' => 'Quay lại đăng nhập',

    'social' => [
        'or' => 'OR',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'not_linked' => 'Tài khoản này chưa được liên kết với bất kỳ tài khoản :provider nào. Vui lòng đăng nhập bằng email và mật khẩu của bạn trước, sau đó liên kết tài khoản :provider của bạn trong trang Cài đặt tài khoản.',
    ],

    'forgot-password' => [
        'title' => 'Yêu cầu đặt lại mật khẩu',
        'label' => 'Quên mật khẩu?',
        'email-label' => 'E-mail',
        'email-content' => 'Nhập địa chỉ email tài khoản của bạn để nhận hướng dẫn đặt lại mật khẩu.',
        'send-email' => 'Gửi email',
    ],

    'checkpoint' => [
        'title' => 'Điểm kiểm tra thiết bị',
        'recovery-code' => 'Mã khôi phục',
        'auth-code' => 'Mã xác thực',
        'is-missing' => 'Nhập một trong các mã khôi phục được tạo khi bạn thiết lập xác thực 2 yếu tố trên tài khoản này để tiếp tục.',
        'is-not-missing' => 'Nhập mã thông báo hai yếu tố do thiết bị của bạn tạo ra.',
        'button' => 'Tiếp tục',
        'lost-device' => 'Tôi đã mất thiết bị của mình',
        'not-lost-device' => 'Tôi có thiết bị của mình',

    ],

    'reset-password' => [
        'new-required' => 'Cần phải có mật khẩu mới.',
        'min-required' => 'Mật khẩu mới của bạn phải dài ít nhất 8 ký tự.',
        'no-match' => 'Mật khẩu mới của bạn không khớp.',
        'email-label' => 'E-mail',
        'new-label' => 'Mật khẩu mới',
        'min-length' => 'Mật khẩu phải có độ dài ít nhất 8 ký tự.',
        'confirm-label' => 'Xác nhận mật khẩu mới',
        'label' => 'Đặt lại mật khẩu',
    ],

    'register' => [
        'no-match' => 'Mật khẩu của bạn không khớp.',
        'namefirst-label' => 'Tên',
        'namelast-label' => 'Họ',
        'email-label' => 'E-mail',
        'username-label' => 'Tên người dùng',
        'password-label' => 'Mật khẩu',
        'min-length' => 'Mật khẩu phải có độ dài ít nhất 8 ký tự.',
        'confirm-label' => 'Xác nhận mật khẩu',
        'label' => 'Đăng ký',
        'create-account' => 'Tạo tài khoản',
    ],

    'failed' => 'Không tìm thấy tài khoản nào phù hợp với thông tin xác thực đó.',

    'two_factor' => [
        'label' => 'Mã thông báo 2 yếu tố',
        'label_help' => 'Tài khoản này yêu cầu lớp xác thực thứ hai để tiếp tục. Vui lòng nhập mã do thiết bị của bạn tạo ra để hoàn tất quá trình đăng nhập này.',
        'checkpoint_failed' => 'Mã thông báo xác thực hai yếu tố không hợp lệ.',
    ],

    'throttle' => 'Quá nhiều lần thử đăng nhập. Vui lòng thử lại sau :seconds giây.',
    'password_requirements' => 'Mật khẩu phải có độ dài ít nhất 8 ký tự và phải là duy nhất cho trang này.',
    '2fa_must_be_enabled' => 'Quản trị viên đã yêu cầu bật Xác thực 2 yếu tố cho tài khoản của bạn để sử dụng Bảng điều khiển.',
];
