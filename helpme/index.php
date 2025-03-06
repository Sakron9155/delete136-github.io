<?php
session_start();
include 'config/db.php';

// ตรวจสอบว่ามี session user_id หรือไม่
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT first_name, last_name, profile_image FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
// ดึงรีวิวพร้อมข้อมูลผู้ใช้และรูปโปรไฟล์
$sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image 
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        ORDER BY r.created_at DESC LIMIT 3";
$result = $conn->query($sql);
$reviews = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteTix - จองตั๋วเครื่องบินออนไลน์</title>
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<style>
/* Location Section Styles */
.location {
    padding: 6rem 0;
    background: #f8f9fa;
}

.location-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 3rem;
    margin-top: 2rem;
}

.address-info {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.address-info h3 {
    color: var(--primary);
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
}

.address-info p {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.address-info i {
    color: var(--primary);
    font-size: 1.2rem;
}

.contact-details {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.map {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Responsive Design for Location */
@media screen and (max-width: 992px) {
    .location-content {
        grid-template-columns: 1fr;
    }
    
    .address-info {
        text-align: center;
    }
    
    .address-info p {
        justify-content: center;
    }
}
</style>

<body>
    <!-- Header Section -->
    <header>
        <nav>
            <div class="">
                <a href="admin/login.php"><img src="images/logo.png" alt="EliteTix Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">หน้าแรก</a></li>
                <li><a href="search.php">ค้นหาเที่ยวบิน</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="booking_history.php">ประวัติการจอง</a></li>
                <?php endif; ?>
                <li><a href="about.php">เพิ่มเติมเกี่ยวกับเรา</a></li>
                <li><a href="index.php">ติดต่อ</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id']) && isset($user)): ?>
                    <div class="user-profile">
                        <span class="username"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="profile-pic">
                        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-btn">เข้าสู่ระบบ</a>
                    <a href="register.php" class="register-btn">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>


    <!-- Hero Video Section -->
    <section class="hero">
        <div class="video-container">
            <video autoplay muted loop playsinline>
                <source src="videos/me.mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>ยินดีต้อนรับสู่ EliteTix</h1>
                <p>บริการจองตั๋วเครื่องบินที่ดีที่สุดสำหรับคุณ</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="search.php" class="cta-button">จองตั๋วเลย</a>
                <?php else: ?>
                    <a href="login.php?redirect=search.php" class="cta-button">กรุณาเข้าสู่ระบบเพื่อจองตั๋ว</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Company Section -->
    <section class="about">
        <div class="container">
            <h2>เกี่ยวกับเรา</h2>
            <div class="about-content">
                <div class="company-info">
                    <h3>EliteTix - ผู้นำด้านการจองตั๋วเครื่องบิน</h3>
                    <p>เราคือผู้ให้บริการจองตั๋วเครื่องบินที่มีประสบการณ์มายาวนาน มุ่งมั่นให้บริการด้วยความใส่ใจ</p>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <h4>1,000+</h4>
                        <p>เที่ยวบินต่อวัน</p>
                    </div>
                    <div class="stat-item">
                        <h4>50,000+</h4>
                        <p>ลูกค้าที่พึงพอใจ</p>
                    </div>
                    <div class="stat-item">
                        <h4>100+</h4>
                        <p>จุดหมายปลายทาง</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="location about">
        <div class="container">
            <h2>ที่ตั้งบริษัท</h2>
            <div class="location-content">
                <div class="address-info">
                    <h3>EliteTix Headquarters</h3>
                    <p><i class="fas fa-map-marker-alt"></i> มหาวิทยาลัยสงขลานครินทร์ วิทยาเขตปัตตานี</p>
                    <p>181 ถ.เจริญประดิษฐ์ ต.รูสะมิแล</p>
                    <p>อ.เมือง จ.ปัตตานี 94000</p>
                    <div class="contact-details">
                        <p><i class="fas fa-phone"></i> 099-301-1820</p>
                        <p><i class="fas fa-envelope"></i> contact@elitetix.com</p>
                    </div>
                </div>
                <div class="map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.6366361436234!2d101.27421147485764!3d6.935830118494689!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31b30405a62902c7%3A0x7c784b9d4d8e183c!2z4Lih4Lir4Liy4Lin4Li04LiX4Lii4Liy4Lil4Lix4Lii4Liq4LiH4LiC4Lil4Liy4LiZ4LiE4Lij4Li04LiZ4LiX4Lij4LmMIOC4p-C4tOC4l-C4ouC4suC5gOC4guC4leC4m-C4seC4leC4leC4suC4meC4tQ!5e0!3m2!1sth!2sth!4v1699789876543!5m2!1sth!2sth"
                        width="100%"
                        height="450"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews">
        <div class="container">
            <h2>รีวิวจากลูกค้า</h2>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="review-form">
                    <h3>แสดงความคิดเห็น</h3>
                    <form action="add_review.php" method="POST">
                        <div class="rating-input">
                            <label>ให้คะแนน:</label>
                            <select name="rating" required>
                                <option value="5">★★★★★</option>
                                <option value="4">★★★★☆</option>
                                <option value="3">★★★☆☆</option>
                                <option value="2">★★☆☆☆</option>
                                <option value="1">★☆☆☆☆</option>
                            </select>
                        </div>
                        <textarea name="comment" placeholder="แสดงความคิดเห็นของคุณ..." required></textarea>
                        <button type="submit">ส่งรีวิว</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="reviews-grid">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <img src="uploads/profiles/<?php echo htmlspecialchars($review['profile_image']); ?>"
                                alt="Profile"
                                class="review-profile-pic">
                            <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                        </div>
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                        <div class="rating">
                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="show-all-reviews" onclick="openReviewModal()">แสดงความคิดเห็นทั้งหมด</button>

            <!-- Modal -->
            <div id="reviewModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeReviewModal()">×</span>
                    <h2>รีวิวทั้งหมด</h2>
                    <div id="allReviews">
                        <!-- รีวิวทั้งหมดจะถูกโหลดที่นี่ด้วย AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Get the modal
        var modal = document.getElementById("reviewModal");

        // Function to open the modal
        function openReviewModal() {
            modal.style.display = "block";
            // Load reviews using AJAX
            fetch('get_all_reviews.php')
                .then(response => response.json())
                .then(data => {
                    let reviewsHtml = '';
                    data.forEach(review => {
                        reviewsHtml += `
                            <div class="review-card">
                                <div class="review-header">
                                    <img src="uploads/profiles/${encodeURIComponent(review.profile_image)}" alt="Profile" class="review-profile-pic">
                                    <h4>${review.first_name.replace(/</g, "<")} ${review.last_name.replace(/</g, "<")}</h4>
                                </div>
                                <p>${review.comment.replace(/</g, "<")}</p>
                                <div class="rating">
                                    ${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}
                                </div>
                            </div>
                        `;
                    });
                    document.getElementById('allReviews').innerHTML = reviewsHtml;
                });
        }

        // Function to close the modal
        function closeReviewModal() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    <script>
        var mainListDiv = document.getElementById("mainListDiv"),
            mediaButton = document.getElementById("mediaButton");

        mediaButton.onclick = function() {

            "use strict";

            mainListDiv.classList.toggle("show_list");
            mediaButton.classList.toggle("active");

        };
    </script>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>EliteTix</h3>
                    <p>บริการจองตั๋วเครื่องบินที่ดีที่สุด</p>
                </div>
                <div class="footer-section">
                    <h3>ติดต่อเรา</h3>
                    <p>อีเมล: contact@elitetix.com</p>
                    <p>โทร: 099-301-1820</p>
                </div>
                <div class="footer-section">
                    <h3>ติดตามเรา</h3>
                    <div class="social-links">
                        <a href="https://www.facebook.com/salif.masaman"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/__sqlif__/"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2023 EliteTix. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>