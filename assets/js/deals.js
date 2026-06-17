document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.stopPropagation(); // giữ lại để không click vào card
        
        const toast = document.getElementById('toastAddedDeals');
        if (!toast) return;

        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    });
});