    </div>
    
    <!-- Footer CSS -->
    <link rel="stylesheet" href="/rtwrs_web/assets/css/footer.css">
    
    <!-- Site Footer Start -->
    <footer class="simple-footer position-relative" role="contentinfo" aria-label="Site footer">
      <div class="container py-4 position-relative">
        <!-- Get Updates Bar -->
        <div class="footer-updates-bar mb-4">
          <div class="updates-text">Get updates and exclusive offers SUBSCRIBE to our newsletter</div>
          <?php if (isset($_GET['subscribed'])): ?>
            <?php if ($_GET['subscribed'] == '1'): ?>
              <div class="alert alert-success py-2 px-3 my-2" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Subscribed successfully!
              </div>
            <?php else: ?>
              <div class="alert alert-danger py-2 px-3 my-2" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Subscription failed. <?php if (!empty($_GET['msg'])) echo htmlspecialchars($_GET['msg']); ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
          <form class="newsletter-form" action="/rtwrs_web/api/subscribe.php" method="POST" aria-label="Newsletter subscription">
            <div class="input-group">
              <label for="subscribe_email_footer" class="visually-hidden">Email address</label>
              <input type="email" id="subscribe_email_footer" name="subscribe_email" class="form-control newsletter-input" placeholder="Enter your email" required aria-required="true">
              <button class="btn newsletter-btn" type="submit" aria-label="Subscribe">
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
              </button>
            </div>
          </form>
        </div>
        <div class="row gy-4 align-items-start">
          <!-- Social Media / Brand -->
          <section class="col-md-3 text-center text-md-start" aria-labelledby="footer-social">
            <img src="/rtwrs_web/assets/images/logo.svg" alt="Rameshwar Traditional Wear Logo">
            <div class="mt-3 fw-semibold text-muted small">Social Media</div>
            <div class="footer-social mt-2" aria-label="Social media">
              <a href="https://instagram.com" target="_blank" rel="noopener" class="footer-social-icon" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
              <a href="https://facebook.com" target="_blank" rel="noopener" class="footer-social-icon" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
              <a href="https://twitter.com" target="_blank" rel="noopener" class="footer-social-icon" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
            </div>
          </section>

          <!-- Informations -->
          <nav class="col-md-3" aria-labelledby="footer-informations">
            <h5 id="footer-informations" class="footer-title">INFORMATIONS</h5>
            <ul class="footer-list">
              <li><a href="contact.php">Contact Us</a></li>
              <li><a href="#" rel="nofollow">Stores</a></li>
              <li><a href="#" rel="nofollow">Blog</a></li>
              <li><a href="#" rel="nofollow">FAQ's</a></li>
              <li><a href="#" rel="nofollow">Franchise Enquiry</a></li>
            </ul>
          </nav>

          <!-- Policies -->
          <nav class="col-md-3" aria-labelledby="footer-policies">
            <h5 id="footer-policies" class="footer-title">POLICIES</h5>
            <ul class="footer-list">
              <li><a href="#" rel="nofollow">Cancellation Policy</a></li>
              <li><a href="#" rel="nofollow">Privacy Policy</a></li>
              <li><a href="#" rel="nofollow">Terms &amp; Conditions</a></li>
              <li><a href="#" rel="nofollow">Website Disclaimer</a></li>
            </ul>
          </nav>

          <!-- Contact Us -->
          <section class="col-md-3" aria-labelledby="footer-contact">
            <h5 id="footer-contact" class="footer-title">CONTACT US</h5>
            <ul class="contact-details list-unstyled mb-0" aria-label="Contact details">
              <li class="contact-item"><span class="label">Mobile:</span><a href="tel:+919999777444" class="contact-link value">+91 99997 77444</a></li>
              <li class="contact-item"><span class="label">Email:</span><a href="mailto:traditionalwear2025@gmail.com" class="contact-link value">traditionalwear2025@gmail.com</a></li>
              <li class="contact-item"><span class="label">Address:</span><a href="https://www.google.com/maps/place/Rameshwar+Traditional+Wear/@21.7682379,72.1475299,17z/data=!3m1!4b1!4m6!3m5!1s0x395f5a7c2a002489:0x88ac1e64daa46b6a!8m2!3d21.7682379!4d72.1475299!16s%2Fg%2F11f_4rw78x?entry=ttu&g_ep=EgoyMDI1MDgwNi4wIKXMDSoASAFQAw%3D%3D" class="contact-link value" target="_blank" rel="noopener">Madhavdarshan Complex, 180, Waghawadi Rd., Pragati Nagar, Rasala Camp, Panwadi, Bhavnagar, Gujarat 364001<br>Maharashtra, India</a></li>
              <li class="contact-item mt-2"><a class="contact-link" target="_blank" rel="noopener" href="https://www.google.com/maps/place/Rameshwar+Traditional+Wear/@21.7682379,72.1475299,17z/data=!3m1!4b1!4m6!3m5!1s0x395f5a7c2a002489:0x88ac1e64daa46b6a!8m2!3d21.7682379!4d72.1475299!16s%2Fg%2F11f_4rw78x?entry=ttu&g_ep=EgoyMDI1MDgwNi4wIKXMDSoASAFQAw%3D%3D">View Location &rarr;</a></li>
            </ul>
          </section>
        </div>

        <!-- Divider -->
        <hr class="footer-sep"/>

        <!-- Footer Bottom -->
        <div class="footer-bottom text-center pt-3 mt-2">
          <?php echo date('Y'); ?> &copy; Rameshwar Traditional Wear. All rights reserved.
        </div>
      </div>
    </footer>
    <!-- Site Footer End -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>