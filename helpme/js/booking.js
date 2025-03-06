// เพิ่ม JavaScript สำหรับการตรวจสอบและ animation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bookingForm');
    const inputs = form.querySelectorAll('input[required]');

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateInput(this);
        });
    });

    function validateInput(input) {
        if (!input.value.trim()) {
            input.classList.add('error');
            input.classList.remove('valid');
        } else {
            input.classList.remove('error');
            input.classList.add('valid');
        }
    }

    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
                input.classList.add('animate__animated', 'animate__shakeX');
            }
        });

        if (!isValid) {
            e.preventDefault();
            showNotification('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
        }
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type} animate__animated animate__fadeIn`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('animate__fadeOut');
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}
