// Hàm giải mã Token JWT từ Google để lấy thông tin người dùng
function decodeJwtResponse(token) {
    let base64Url = token.split('.')[1];
    let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    let jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
}

// Xử lý sau khi người dùng chọn tài khoản Google thành công
function handleCredentialResponse(response) {
    const userData = decodeJwtResponse(response.credential);
    
    // Lưu thông tin người dùng vào LocalStorage để trang Dashboard có thể sử dụng
    localStorage.setItem('nexus_user', JSON.stringify({
        name: userData.name,
        email: userData.email,
        picture: userData.picture
    }));
    
    // Chuyển hướng sang trang Dashboard
    window.location.href = "dashboard.html"; 
}

// Hàm kích hoạt popup đăng nhập tùy chỉnh
function triggerNexusLogin() {
    google.accounts.id.prompt();
}