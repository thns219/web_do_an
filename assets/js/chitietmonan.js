    document.addEventListener('DOMContentLoaded', function () {
      const qtySpan  = document.getElementById('quantityValue');
      const btnMinus = document.getElementById('decreaseBtn');
      const btnPlus  = document.getElementById('increaseBtn');
      const addBtn   = document.querySelector('.add-to-cart-btn');
      const MIN_QTY = 1;
      const MAX_QTY = 99;

      function getQty() {
        let v = parseInt(qtySpan.textContent, 10);
        if (isNaN(v) || v < MIN_QTY) v = MIN_QTY;
        if (v > MAX_QTY) v = MAX_QTY;
        qtySpan.textContent = v;
        return v;
      }

      if (btnMinus) {
        btnMinus.addEventListener('click', function () {
          let v = getQty();
          if (v > MIN_QTY) {
            qtySpan.textContent = v - 1;
          }
        });
      }

      if (btnPlus) {
        btnPlus.addEventListener('click', function () {
          let v = getQty();
          if (v < MAX_QTY) {
            qtySpan.textContent = v + 1;
          }
        });
      }

      // Gallery đổi ảnh theo thumbnail
      const mainImage = document.getElementById('mainImage');
      const thumbs = document.querySelectorAll('.thumbnail');
      if (mainImage && thumbs.length > 0) {
        thumbs.forEach(thumb => {
          thumb.addEventListener('click', function () {
            thumbs.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            const img = thumb.querySelector('img');
            if (img) {
              mainImage.src = img.src;
            }
          });
        });
      }

      // Nút yêu thích
      const favBtn = document.getElementById('favoriteBtn');
      if (favBtn) {
        favBtn.addEventListener('click', function () {
          favBtn.classList.toggle('active');
        });
      }

      // Thêm giỏ: nối qty vào URL + hiện toast
      if (addBtn) {
        addBtn.addEventListener('click', function (e) {
          const baseHref = addBtn.getAttribute('data-base-href') || addBtn.getAttribute('href');
          if (!baseHref) return;

          const qty = getQty();
          const url = baseHref + '&qty=' + encodeURIComponent(qty);

          const toast = document.getElementById('toastAddedDeals');
          if (toast) {
            e.preventDefault();
            toast.classList.add('show');
            setTimeout(() => {
              toast.classList.remove('show');
              window.location.href = url;
            }, 700);
          } else {
            // fallback: chỉnh href rồi cho browser đi tiếp
            addBtn.setAttribute('href', url);
          }
        });
      }
    });
