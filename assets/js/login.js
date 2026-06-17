document.addEventListener('DOMContentLoaded', function () {
  const container  = document.getElementById('container');
  const signUpBtn  = document.getElementById('signUp');
  const signInBtn  = document.getElementById('signIn');

  // đặt title theo trạng thái ban đầu
  if (container && container.classList.contains('right-panel-active')) {
    document.title = 'Đăng ký - Ăn Húp Hội';
  } else {
    document.title = 'Đăng nhập - Ăn Húp Hội';
  }

  if (signUpBtn) {
    signUpBtn.addEventListener('click', function () {
      if (container) container.classList.add('right-panel-active');
      document.title = 'Đăng ký - Ăn Húp Hội';
    });
  }

  if (signInBtn) {
    signInBtn.addEventListener('click', function () {
      if (container) container.classList.remove('right-panel-active');
      document.title = 'Đăng nhập - Ăn Húp Hội';
    });
  }

  // ============= ĐẾM NGƯỢC RESEND OTP + TEXT =============
  function initResend(buttonId, labelId) {
    const btn   = document.getElementById(buttonId);
    const label = document.getElementById(labelId);
    if (!btn) return;

    let remaining = parseInt(btn.dataset.remaining || '0', 10);
    if (isNaN(remaining) || remaining < 0) remaining = 0;

    function updateLabel() {
      if (!label) return;
      if (remaining > 0) {
        label.textContent = `Bạn có thể gửi lại OTP sau ${remaining}s`;
      } else {
        label.textContent = 'Bạn có thể bấm "Gửi lại OTP" nếu chưa nhận được mã.';
      }
    }

    if (remaining <= 0) {
      btn.disabled = false;
      btn.textContent = 'Gửi lại OTP';
      updateLabel();
      return;
    }

    btn.disabled = true;

    const updateButton = () => {
      btn.textContent = `Gửi lại OTP (${remaining}s)`;
      updateLabel();
    };

    updateButton();

    const timer = setInterval(() => {
      remaining--;
      if (remaining > 0) {
        updateButton();
      } else {
        clearInterval(timer);
        btn.disabled = false;
        btn.textContent = 'Gửi lại OTP';
        remaining = 0;
        updateLabel();
        btn.dataset.remaining = '0';
      }
    }, 1000);
  }

  // Đăng ký
  initResend('btnResendRegister', 'registerCountdownText');
  // Quên mật khẩu
  initResend('btnResendForgot', 'forgotCountdownText');
});
