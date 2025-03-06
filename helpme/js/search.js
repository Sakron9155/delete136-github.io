document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.search-form');
    const originSelect = document.getElementById('origin');
    const destinationSelect = document.getElementById('destination');
    const departureDateInput = document.getElementById('departure_date');

    // ตั้งค่าวันที่ขั้นต่ำเป็นวันปัจจุบัน
    const today = new Date().toISOString().split('T')[0];
    departureDateInput.min = today;

    // ตรวจสอบว่าต้นทางและปลายทางไม่เหมือนกัน
    form.addEventListener('submit', function(e) {
        if (originSelect.value === destinationSelect.value) {
            e.preventDefault();
            alert('กรุณาเลือกสนามบินต้นทางและปลายทางที่แตกต่างกัน');
        }
    });

    // อัพเดทตัวเลือกปลายทางเมื่อเลือกต้นทาง
    originSelect.addEventListener('change', function() {
        const selectedOrigin = this.value;
        Array.from(destinationSelect.options).forEach(option => {
            option.disabled = option.value === selectedOrigin;
        });
    });
});
