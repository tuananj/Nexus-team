// popup.js
async function generateTOTP(secret) {
    try {
        const base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        let bits = "";
        const cleanSecret = secret.replace(/\s/g, "").toUpperCase();
        for (let i = 0; i < cleanSecret.length; i++) {
            let val = base32chars.indexOf(cleanSecret.charAt(i));
            if (val !== -1) bits += val.toString(2).padStart(5, '0');
        }
        const bytes = new Uint8Array(bits.match(/.{1,8}/g).map(b => parseInt(b, 2)));
        let time = BigInt(Math.floor(Date.now() / 1000 / 30));
        const timeBytes = new Uint8Array(8);
        for (let i = 7; i >= 0; i--) { timeBytes[i] = Number(time & 0xffn); time >>= 8n; }
        const key = await crypto.subtle.importKey("raw", bytes, { name: "HMAC", hash: "SHA-1" }, false, ["sign"]);
        const signature = await crypto.subtle.sign("HMAC", key, timeBytes);
        const hmac = new Uint8Array(signature);
        const offset = hmac[hmac.length - 1] & 0xf;
        const code = ((hmac[offset] & 0x7f) << 24 | (hmac[offset + 1] & 0xff) << 16 | (hmac[offset + 2] & 0xff) << 8 | (hmac[offset + 3] & 0xff)) % 1000000;
        return code.toString().padStart(6, '0');
    } catch (e) { return "000000"; }
}

document.addEventListener('DOMContentLoaded', () => {
    const render = async () => {
        const list = document.getElementById('account-list');
        const data = JSON.parse(localStorage.getItem('mfa_accounts') || '[]');
        if(!list) return;
        list.innerHTML = data.length ? '' : '<div class="empty-state">Huy chưa có tài khoản nào!</div>';
        for (const a of data) {
            const code = await generateTOTP(a.secret);
            const timeLeft = 30 - (Math.floor(Date.now() / 1000) % 30);
            list.innerHTML += `
                <div class="account-item" style="display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #eee">
                    <div>
                        <div style="font-size:10px; color:#666">${a.name}</div>
                        <div style="font-size:24px; font-weight:bold; color:#1a73e8">${code.slice(0,3)} ${code.slice(3,6)}</div>
                    </div>
                    <div style="width:20px; height:20px; border-radius:50%; background:conic-gradient(#1a73e8 ${timeLeft/30*100}%, #eee 0)"></div>
                </div>`;
        }
    };

    setInterval(render, 1000);

    document.getElementById('scanBtn').onclick = () => {
        chrome.tabs.captureVisibleTab(null, {format:'png'}, (url) => {
            const img = new Image(); img.src = url;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width; canvas.height = img.height;
                const ctx = canvas.getContext('2d'); ctx.drawImage(img,0,0);
                const qr = jsQR(ctx.getImageData(0,0,canvas.width,canvas.height).data, canvas.width, canvas.height);
                if(qr) {
                    const params = new URL(qr.data);
                    const secret = params.searchParams.get("secret");
                    // Lấy tên User từ link: otpauth://totp/Tên_Ở_Đây?secret=...
                    const name = decodeURIComponent(params.pathname.split(':').pop());
                    let accounts = JSON.parse(localStorage.getItem('mfa_accounts') || '[]');
                    if(!accounts.some(x => x.secret === secret)) {
                        accounts.push({name: name, secret: secret});
                        localStorage.setItem('mfa_accounts', JSON.stringify(accounts));
                        alert("✅ Đã lưu vĩnh viễn cho: " + name);
                    }
                    render();
                }
            };
        });
    };
});