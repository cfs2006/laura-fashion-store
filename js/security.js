/**
 * L'AURA Security Script
 * Bảo vệ mã nguồn khỏi các thao tác cơ bản của lập trình viên
 */

// 1. Chặn chuột phải (Context Menu)
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
    });

    // 2. Chặn các phím tắt nguy hiểm (F12, Ctrl+U, Ctrl+Shift+I, v.v...)
    document.addEventListener('keydown', (e) => {
        // F12
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
        }
        
        // Ctrl + Shift + I (DevTools)
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.keyCode === 73)) {
            e.preventDefault();
        }
        
        // Ctrl + Shift + J (DevTools Console)
        if (e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j' || e.keyCode === 74)) {
            e.preventDefault();
        }

        // Ctrl + Shift + C (Inspect Element)
        if (e.ctrlKey && e.shiftKey && (e.key === 'C' || e.key === 'c' || e.keyCode === 67)) {
            e.preventDefault();
        }

        // Ctrl + U (View Source)
        if (e.ctrlKey && (e.key === 'U' || e.key === 'u' || e.keyCode === 85)) {
            e.preventDefault();
        }
    });

    // 3. Chặn bôi đen nội dung (tùy chọn nhưng hiệu quả cho chống copy)
    document.addEventListener('selectstart', (e) => {
        // Có thể loại trừ input, textarea để người dùng vẫn nhập liệu được
        const tagName = e.target.tagName.toLowerCase();
        if (tagName !== 'input' && tagName !== 'textarea') {
            e.preventDefault();
        }
    });
