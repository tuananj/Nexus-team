async function scanScreenForQR() {
    try {
        // 1. Chụp ảnh màn hình hiện tại (Yêu cầu quyền từ trình duyệt)
        const stream = await navigator.mediaDevices.getDisplayMedia({ video: true });
        const video = document.createElement('video');
        video.srcObject = stream;
        video.play();

        // 2. Đưa ảnh vào Canvas để xử lý pixel
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        // Đợi video tải xong rồi chụp 1 khung hình
        setTimeout(() => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // 3. Dùng thư viện jsQR để tìm mã QR trong ảnh
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                console.log("Tìm thấy mã QR!", code.data); // data này chính là chuỗi chứa Secret Key
                // Tắt luồng quay màn hình
                stream.getTracks().forEach(track => track.stop());
            } else {
                console.log("Không tìm thấy mã QR nào trên màn hình Huy ơi!");
            }
        }, 1000);
    } catch (err) {
        console.error("Lỗi quét màn hình: ", err);
    }
}