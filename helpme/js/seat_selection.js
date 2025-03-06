document.addEventListener('DOMContentLoaded', function() {
    const seats = document.querySelectorAll('.seat input[type="checkbox"]');
    const totalPriceElement = document.getElementById('total-price');
    
    seats.forEach(seat => {
        seat.addEventListener('change', calculateTotal);
    });
    
    function calculateTotal() {
        let total = 0;
        seats.forEach(seat => {
            if (seat.checked) {
                const priceMultiplier = seat.closest('.seat').dataset.price;
                total += parseFloat(priceMultiplier);
            }
        });
        totalPriceElement.textContent = total.toFixed(2);
    }
});
class SeatSelector {
    constructor(maxSeats) {
        this.maxSeats = maxSeats;
        this.selectedSeats = new Set();
        this.init();
    }

    init() {
        this.setupSeatMap();
        this.setupPassengerSelects();
        this.addLegendTooltips();
    }

    setupSeatMap() {
        const seats = document.querySelectorAll('.seat');
        seats.forEach(seat => {
            seat.addEventListener('click', (e) => this.handleSeatClick(e));
            
            // Add airplane icon
            const icon = document.createElement('i');
            icon.className = 'material-icons seat-icon';
            icon.textContent = 'airline_seat_recline_normal';
            seat.appendChild(icon);
        });
    }

    handleSeatClick(e) {
        const seat = e.currentTarget;
        if (seat.classList.contains('occupied')) return;

        if (seat.classList.contains('selected')) {
            seat.classList.remove('selected');
            this.selectedSeats.delete(seat.dataset.seatId);
        } else {
            if (this.selectedSeats.size >= this.maxSeats) {
                this.showNotification(`สามารถเลือกได้สูงสุด ${this.maxSeats} ที่นั่ง`, 'warning');
                return;
            }
            seat.classList.add('selected');
            this.selectedSeats.add(seat.dataset.seatId);
        }

        this.updatePassengerSelects();
        this.updatePriceCalculation();
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type} animate__animated animate__fadeIn`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('animate__fadeOut');
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    updatePriceCalculation() {
        let totalMarkup = 0;
        this.selectedSeats.forEach(seatId => {
            const seat = document.querySelector(`[data-seat-id="${seatId}"]`);
            totalMarkup += parseFloat(seat.dataset.price);
        });

        document.getElementById('total-price').textContent = 
            `ราคาเพิ่มเติมรวม: ${totalMarkup.toLocaleString()} บาท`;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    const seatSelector = new SeatSelector(maxPassengers);
});

