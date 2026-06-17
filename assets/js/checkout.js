const form = document.getElementById("formuserr");
const updateUser = document.getElementById("updateUser");

updateUser.onclick = function () {
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";   // nếu đang ẩn → hiện
    } else {
        form.style.display = "none";    // nếu đang hiện → ẩn
    }
}
