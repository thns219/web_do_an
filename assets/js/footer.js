// Thêm hiệu ứng hiện thông báo khi click social icon
document.addEventListener("DOMContentLoaded", () => {
  const socials = document.querySelectorAll(".social-links a");
  socials.forEach(icon => {
    icon.addEventListener("click", (e) => {
      e.preventDefault();
      alert("Bạn vừa click vào " + icon.querySelector("i").className.replace("fab fa-", "").toUpperCase());
    });
  });
});
