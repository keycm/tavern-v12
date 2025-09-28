<?php
session_start();
include 'config.php'; // Include your database configuration

// Fetch hero slides
$hero_slides = [];
$sql_slides = "SELECT * FROM hero_slides ORDER BY created_at DESC";
$result_slides = $conn->query($sql_slides);
if ($result_slides->num_rows > 0) {
    while($row = $result_slides->fetch_assoc()) {
        $hero_slides[] = $row;
    }
}


// Fetch unrated reservations for the logged-in user
$unrated_reservations = [];
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    $user_id = $_SESSION['user_id'];
    $sql_unrated = "SELECT r.reservation_id, r.res_date FROM reservations r LEFT JOIN testimonials t ON r.reservation_id = t.reservation_id WHERE r.user_id = ? AND t.id IS NULL AND r.status = 'Confirmed' ORDER BY r.res_date DESC";
    if ($stmt = $conn->prepare($sql_unrated)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $unrated_reservations[] = $row;
        }
    }
}

// Fetch featured testimonials
$featured_testimonials = [];
$sql_testimonials = "SELECT t.*, u.username, u.avatar FROM testimonials t JOIN users u ON t.user_id = u.user_id WHERE t.is_featured = 1 ORDER BY t.created_at DESC LIMIT 3";
$result_testimonials = $conn->query($sql_testimonials);
if ($result_testimonials->num_rows > 0) {
    while ($row = $result_testimonials->fetch_assoc()) {
        $featured_testimonials[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico</title>
    <link rel="stylesheet" href="CSS/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hero-bg-video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
            /* z-index: -1; <-- This was the bug, removing it */
        }

        a.event-card-link { text-decoration: none; color: inherit; }
        .rating-form-section { background-color: #f4f4f4; }
        .rating-form { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; margin-bottom: 15px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 2rem; color: #ddd; cursor: pointer; transition: color 0.2s; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f5b301; }
        .rating-form .form-group { margin-bottom: 20px; text-align: left; }
        .rating-form .form-group label { display: block; font-weight: 500; margin-bottom: 8px; color: #333; }
        .rating-form select, .rating-form textarea { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 5px; font-family: 'Mada', sans-serif; font-size: 1em; color: #333; box-sizing: border-box; background-color: #fff; -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: .65em auto; }
        .rating-form textarea { background-image: none; }
        .testimonial-card { display: flex; flex-direction: column; height: 100%; }
        .testimonial-card p { flex-grow: 1; }
        .guest-info { margin-top: auto; }

        /* --- INLINE SLIDER STYLES FIX --- */
        .slider-container {
            position: relative;
        }
        .slider-wrapper {
            display: grid; /* Default to grid for desktop */
            grid-template-columns: repeat(3, 1fr);
            gap: 29px;
        }
        .slider-item {
             height: 100%;
        }
        .slider-btn {
            display: none; /* Hide buttons by default on desktop */
        }

        @media (max-width: 768px) {
            .slider-container {
                overflow: hidden;
            }
            .slider-wrapper {
                display: flex; /* Switch to flex for mobile slider */
                grid-template-columns: none;
                gap: 0;
                transition: transform 0.5s ease-in-out;
            }
            .slider-item {
                flex: 0 0 100%;
                box-sizing: border-box;
                padding: 0 10px;
            }
            .slider-item .specialty-card,
            .slider-item .event-card,
            .slider-item .testimonial-card {
                height: 100%;
                margin: 0;
            }
            .slider-btn {
                display: block; /* Show buttons on mobile */
                position: absolute;
                top: 40%;
                transform: translateY(-50%);
                background-color: rgba(0, 0, 0, 0.5);
                color: white;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                font-size: 20px;
                cursor: pointer;
                z-index: 10;
            }
            .prev-btn { left: 5px; }
            .next-btn { right: 5px; }
        }
    </style>
</head>

<body>

    <?php include 'partials/header.php'; ?>

    <section class="hero-section">
        <div class="slideshow-container">
            <?php if (!empty($hero_slides)): ?>
                <?php foreach ($hero_slides as $index => $slide): ?>
                    <div class="mySlides fade">
                        <?php if ($slide['media_type'] === 'video' && !empty($slide['video_path'])): ?>
                            <video autoplay muted playsinline class="hero-bg-video">
                                <source src="<?php echo htmlspecialchars($slide['video_path']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Hero Image" class="hero-bg-image">
                            <div class="hero-overlay">
                                <div class="hero-text">
                                    <h1><?php echo htmlspecialchars($slide['title']); ?></h1>
                                    <span class="est-year-hero"><?php echo htmlspecialchars($slide['subtitle']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div style="text-align:center; position: absolute; bottom: 20px; width: 100%; z-index: 2;">
                    <?php foreach ($hero_slides as $index => $slide): ?>
                        <span class="dot" onclick="currentSlide(<?php echo $index + 1; ?>)"></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="mySlides fade"><img src="images/1st.jpg" alt="Tavern Publico Interior" class="hero-bg-image"><div class="hero-overlay"><div class="hero-text"><h1>TAVERN PUBLICO</h1><span class="est-year-hero">EST ★ 2024</span></div></div></div>
                <div class="mySlides fade"><img src="images/2nd.jpg" alt="Dining Area" class="hero-bg-image"><div class="hero-overlay"><div class="hero-text"><h1>Experience Fine Dining</h1><span class="est-year-hero">Fresh Ingredients, Exquisite Taste</span></div></div></div>
                <div class="mySlides fade"><img src="images/3rd.jpg" alt="Bar Area" class="hero-bg-image"><div class="hero-overlay"><div class="hero-text"><h1>Craft Cocktails & Spirits</h1><span class="est-year-hero">Unwind and Indulge</span></div></div></div>
                <div class="mySlides fade"><img src="images/4th.jpg" alt="Outdoor Seating" class="hero-bg-image"><div class="hero-overlay"><div class="hero-text"><h1>Perfect Ambiance</h1><span class="est-year-hero">For Every Occasion</span></div></div></div>
                <div style="text-align:center; position: absolute; bottom: 20px; width: 100%; z-index: 2;"><span class="dot" onclick="currentSlide(1)"></span><span class="dot" onclick="currentSlide(2)"></span><span class="dot" onclick="currentSlide(3)"></span><span class="dot" onclick="currentSlide(4)"></span></div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($unrated_reservations)): ?>
    <section class="rating-form-section common-padding">
        <div class="container">
            <h2>Rate Your Recent Visit</h2>
            <div class="rating-form"><form id="ratingForm"><div class="form-group"><label for="reservation_id">Select a reservation to rate:</label><select name="reservation_id" id="reservation_id"><?php foreach ($unrated_reservations as $res): ?><option value="<?php echo $res['reservation_id']; ?>">Reservation on <?php echo htmlspecialchars($res['res_date']); ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Your Rating:</label><div class="star-rating"><input type="radio" id="1-star" name="rating" value="1" /><label for="1-star" class="star">★</label><input type="radio" id="2-stars" name="rating" value="2" /><label for="2-stars" class="star">★</label><input type="radio" id="3-stars" name="rating" value="3" /><label for="3-stars" class="star">★</label><input type="radio" id="4-stars" name="rating" value="4" /><label for="4-stars" class="star">★</label><input type="radio" id="5-stars" name="rating" value="5" /><label for="5-stars" class="star">★</label></div></div><div class="form-group"><label for="comment">Leave a comment:</label><textarea name="comment" id="comment" rows="4" placeholder="Tell us about your experience..."></textarea></div><button type="submit" class="btn btn-primary" style="width:100%;">Submit Rating</button></form></div>
        </div>
    </section>
    <?php endif; ?>

    <section class="reserve-now-section common-padding">
        <div class="container">
            <h2>Reserve Your Table</h2>
            <p>Book your spot for an unforgettable dining experience at Tavern Publico.</p>
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                echo '<a href="reserve.php" class="btn btn-primary">Reserve Now</a>';
            } else {
                echo '<button class="btn btn-primary signin-button">Reserve Now</button>';
            }
            ?>
        </div>
    </section>

    <section class="specialties-section common-padding">
        <div class="container">
            <h2>Our Specialties</h2>
            <div class="slider-container">
                <div class="slider-wrapper">
                    <?php 
                    $sql_specialties = "SELECT * FROM menu WHERE category = 'Specialty' ORDER BY RAND() LIMIT 3";
                    $result_specialties = $conn->query($sql_specialties);
                    if ($result_specialties->num_rows > 0) {
                        while ($row = $result_specialties->fetch_assoc()) {
                            echo '<div class="slider-item">';
                            echo '  <div class="specialty-card">';
                            echo '      <img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                            echo '      <h3>' . htmlspecialchars($row['name']) . '</h3>';
                            echo '      <p>' . htmlspecialchars($row['description']) . '</p>';
                            echo '      <div class="price-arrow"><span class="price">₱' . number_format($row['price'], 2) . '</span></div>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <button class="slider-btn prev-btn">&lt;</button>
                <button class="slider-btn next-btn">&gt;</button>
            </div>
            <a href="menu.php" class="btn btn-secondary">View Full Menu</a>
        </div>
    </section>

    <section class="our-story-section common-padding">
        <div class="container">
            <h2>Our Story</h2>
            <div class="story-content"><div class="story-image"><img src="images/story.jpg" alt="Our Story Image"></div><div class="story-text"><h2>Our Story</h2><p>Founded in 2024, Tavern Publico was born from a passion for bringing together exceptional craft food and drinks in a welcoming environment. Our chefs use locally-sourced ingredients to create memorable dishes that honor tradition while embracing innovation.</p><p>Every visit to Tavern Publico is an opportunity to experience the warmth of our hospitality and the quality of our cuisine.</p><a href="about.php" class="btn btn-outline-dark">Learn More About Us</a></div></div>
        </div>
    </section>

    <section class="upcoming-events-section common-padding">
        <div class="container">
            <h2>Upcoming Events</h2>
            <div class="slider-container">
                <div class="slider-wrapper">
                    <?php 
                    $sql_events = "SELECT * FROM events ORDER BY date DESC LIMIT 3";
                    $result_events = $conn->query($sql_events);
                    if ($result_events->num_rows > 0) {
                        while ($row = $result_events->fetch_assoc()) {
                            $start_date_formatted = date("l, F j, Y", strtotime($row['date']));
                            $date_display = $start_date_formatted;
                            if (!empty($row['end_date'])) {
                                $end_date_formatted = date("l, F j, Y", strtotime($row['end_date']));
                                if ($start_date_formatted !== $end_date_formatted) {
                                    $date_display .= " - " . $end_date_formatted;
                                }
                            }
                            echo '<div class="slider-item">';
                            echo '  <div class="event-card">';
                            echo '      <img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                            echo '      <span class="event-date">' . htmlspecialchars($date_display) . '</span>';
                            echo '      <h3>' . htmlspecialchars($row['title']) . '</h3>';
                            echo '      <p>' . substr(htmlspecialchars($row['description']), 0, 100) . '...</p>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <button class="slider-btn prev-btn">&lt;</button>
                <button class="slider-btn next-btn">&gt;</button>
            </div>
            <a href="events.php" class="btn btn-secondary">View All Events</a>
        </div>
    </section>

    <section class="guest-testimonials-section common-padding">
        <div class="container">
            <h2>What Our Guests Say</h2>
            <div class="slider-container">
                <div class="slider-wrapper">
                    <?php if (!empty($featured_testimonials)): ?>
                        <?php foreach ($featured_testimonials as $testimonial): ?>
                            <div class="slider-item">
                                <div class="testimonial-card">
                                    <div class="stars"><?php echo str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']); ?></div>
                                    <p>"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                                    <div class="guest-info">
                                        <?php $avatar_path = !empty($testimonial['avatar']) && file_exists($testimonial['avatar']) ? $testimonial['avatar'] : 'images/default_avatar.png'; ?>
                                        <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="<?php echo htmlspecialchars($testimonial['username']); ?>">
                                        <div class="guest-details"><span class="guest-name"><?php echo htmlspecialchars($testimonial['username']); ?></span></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="slider-item"><div class="testimonial-card"><div class="stars">★★★★★</div><p>"The food was absolutely amazing! Juicy, flavorful, and perfectly cooked..."</p><div class="guest-info"><img src="images/OIP.webp" alt="Maria Santos"><div class="guest-details"><span class="guest-name">Maria Santos</span></div></div></div></div>
                        <div class="slider-item"><div class="testimonial-card"><div class="stars">★★★★★</div><p>"Tavern Publico has become our go-to spot for date nights..."</p><div class="guest-info"><img src="images/man-3d-avatar-4-1024.webp" alt="John Doe"><div class="guest-details"><span class="guest-name">John Doe</span></div></div></div></div>
                        <div class="slider-item"><div class="testimonial-card"><div class="stars">★★★★★</div><p>"We hosted our company dinner at Tavern Publico and it was perfect!..."</p><div class="guest-info"><img src="images/ICON-MALE_Male-And-Female-Review-Messages.png" alt="Anna Cruz"><div class="guest-details"><span class="guest-name">Anna Cruz</span></div></div></div></div>
                    <?php endif; ?>
                </div>
                <button class="slider-btn prev-btn">&lt;</button>
                <button class="slider-btn next-btn">&gt;</button>
            </div>
        </div>
    </section>

    <section class="call-to-action-section">
        <div class="container">
            <div class="cta-content"><h2>Ready to Experience Tavern Publico?</h2><p>Join us for an unforgettable dining experience. Whether you're planning a romantic dinner, family gathering, or just want to enjoy great food and drinks, we're here to serve you.</p><div class="cta-buttons"><a href="reserve.php" class="btn btn-outline-white">Reserve a Table</a><a href="menu.php" class="btn btn-outline-white">View Our Menu</a><a href="contact.php" class="btn btn-outline-white">Contact Us</a></div></div>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>
    <?php include 'partials/Signin-Signup.php'; ?>

    <script src="JS/main.js"></script>
    <script>
        // --- INLINED HERO SLIDESHOW SCRIPT ---
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll(".slideshow-container .mySlides");
            const dots = document.querySelectorAll(".slideshow-container .dot");
            let slideIndex = 0; // Use 0-based index for arrays

            if (slides.length === 0) return;

            // Make the navigation function globally accessible for the onclick attributes
            window.currentSlide = function(n) {
                // n is 1-based from HTML, convert to 0-based for the function
                moveToSlide(n - 1);
            }

            function moveToSlide(n) {
                // Stop and reset any currently playing video from the PREVIOUS slide
                const oldVideo = slides[slideIndex] ? slides[slideIndex].querySelector("video.hero-bg-video") : null;
                if (oldVideo) {
                    oldVideo.pause();
                    oldVideo.onended = null; // Important: remove the old event listener
                }

                // Hide the current slide
                slides[slideIndex].style.display = "none";
                if(dots[slideIndex]) dots[slideIndex].classList.remove("active");
                
                // Set the new slide index, looping if necessary
                slideIndex = n;
                if (slideIndex >= slides.length) {
                    slideIndex = 0; // Loop back to the first slide
                }
                if (slideIndex < 0) {
                    slideIndex = slides.length - 1; // Loop to the last slide
                }

                const currentSlide = slides[slideIndex];
                const newVideo = currentSlide.querySelector("video.hero-bg-video");

                // Show the new slide and activate its dot
                if(dots[slideIndex]) dots[slideIndex].classList.add("active");
                currentSlide.style.display = "block";

                // Handle the new slide's media
                if (newVideo) {
                    newVideo.currentTime = 0; // Rewind the video
                    newVideo.play().catch(error => {
                        console.error("Video autoplay failed.", error);
                    });
                    
                    // Add an event listener that triggers ONLY when this specific video ends
                    newVideo.onended = () => {
                        moveToSlide(slideIndex + 1); // Advance to the next slide
                    };
                } 
                // No automatic timer for images. The slideshow will stop on image slides.
            }

            // Initialize the slideshow
            moveToSlide(0);
        });
        
        // --- Other page-specific scripts ---
        document.addEventListener('DOMContentLoaded', () => {
            const ratingForm = document.getElementById('ratingForm');
            if (ratingForm) {
                ratingForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('submit_rating.php', { method: 'POST', body: formData }).then(response => response.json()).then(data => { alert(data.message); if (data.success) { this.closest('.rating-form-section').remove(); } });
                });
            }
        });
    </script>
</body>

</html>