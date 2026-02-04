<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header_public.php';

?>

<section class="about-wrap container">

  <div class="about-card glass-card">

    <!-- ABOUT / HERO -->
    <div class="about-section about-intro">
      <img src="assets/images/logo.jpg" alt="ÉtudeSync" class="about-logo" />
      <h1>ÉtudeSync</h1>
      <p class="lead">Study • Connect • Grow — All in one space.</p>
      <p class="about-text">
        ÉtudeSync is a unified study workspace built for students, teachers, and professionals.
        Imagine a neat study desk where everything you need is within reach — notes, rooms,
        flashcards, quizzes, planners and more — all in one beautiful, distraction-free app.
      </p>
    </div>

    <!-- TESTIMONIALS MARQUEE (highlighted blocks) -->
    <div class="about-section testimonials-section">
      <h2>What our users say</h2>

      <div class="testimonials-marquee" aria-hidden="false">
        <div class="testimonials-track">
          <?php
          // Example testimonials — replace with real ones later or pull from DB
          $testimonials = [
            ["text"=>"ÉtudeSync changed how I study — everything in one place.", "who"=>"Priya, Student"],
            ["text"=>"Creating rooms for revision saved us hours.", "who"=>"Sam, Student"],
            ["text"=>"Teachers can easily share polls & materials — so useful.", "who"=>"Mr. Rao, Teacher"],
            ["text"=>"My productivity skyrocketed with the Pomodoro timers.", "who"=>"Rohit, Developer"],
            ["text"=>"Flashcards are simple and effective for quick reviews.", "who"=>"Anya, University"],
            ["text"=>"I manage tasks and notes without switching apps.", "who"=>"Lakshmi, Professional"],
            ["text"=>"The interface is beautiful and calming — I love it.", "who"=>"Maya, Tutor"],
            ["text"=>"Our study group stays organised and engaged.", "who"=>"Rahul, Student"],
            ["text"=>"AssessArena quizzes helped me prepare for exams.", "who"=>"Nisha, Student"],
            ["text"=>"MindSpace journaling helped me keep consistent habits.", "who"=>"Karthik, Teacher"]
          ];
          // render twice for smoother looping
          for ($r=0;$r<2;$r++):
            foreach ($testimonials as $i => $t): ?>
              <div class="testimonial-block">
                <p class="testimonial-text">“<?= htmlspecialchars($t['text']) ?>”</p>
                <div class="testimonial-who"><?= htmlspecialchars($t['who']) ?></div>
              </div>
            <?php endforeach;
          endfor;
          ?>
        </div>
      </div>
    </div>

    <!-- AWARDS & MILESTONES -->
    <div class="about-section awards-section">
      <h2>Awards & Milestones</h2>
      <div class="awards-grid">
        <div class="award">
          <div class="award-num">🏆</div>
          <div class="award-body">
            <div class="award-title">EdTech Excellence 2025</div>
            <div class="award-sub">Recognised for innovation in learning</div>
          </div>
        </div>

        <div class="award">
          <div class="award-num">⭐</div>
          <div class="award-body">
            <div class="award-title">Top 10 Study Apps</div>
            <div class="award-sub">Featured by EduMag</div>
          </div>
        </div>

        <div class="award">
          <div class="award-num">📈</div>
          <div class="award-body">
            <div class="award-title">1,00,000+ Tasks Completed</div>
            <div class="award-sub">By ÉtudeSync users in 6 months</div>
          </div>
        </div>

        <div class="award">
          <div class="award-num">🌍</div>
          <div class="award-body">
            <div class="award-title">Available in 35+ countries</div>
            <div class="award-sub">Multiple languages & growing</div>
          </div>
        </div>
      </div>
    </div>

    <!-- USERS PHOTO MARQUEE -->
    <div class="about-section photos-section">
      <h2>People using ÉtudeSync</h2>

      <div class="photos-marquee">
        <div class="photos-track">
          <?php
          // Image placeholders user1..user10 — add these files to assets/images/
          for ($i=1; $i<=10; $i++): ?>
            <div class="photo-cell">
              <img src="assets/images/user<?= $i ?>.jpg" alt="ÉtudeSync user <?= $i ?>" loading="lazy" />
            </div>
          <?php endfor;
          // duplicate set for smooth loop
          for ($i=1; $i<=10; $i++): ?>
            <div class="photo-cell">
              <img src="assets/images/user<?= $i ?>.jpg" alt="ÉtudeSync user <?= $i ?>" loading="lazy" />
            </div>
          <?php endfor; ?>
        </div>
      </div>

    </div>

    <!-- JOIN CTA -->
    <div class="about-section join-section">
      <div class="join-inner">
        <div class="join-left">
          <div class="big-number">Join over <span class="join-count">4,00,000+</span></div>
          <div class="join-sub">students & teachers & professionals on ÉtudeSync</div>
        </div>
        <div class="join-right">
          <a href="get_started.php" class="btn primary large"> Start Your Journey</a>
        </div>
      </div>
    </div>

  </div> <!-- .about-card -->
</section>

<!-- small script: pause marquees on hover -->
<script>
(function(){
  // pause/resume CSS animation by toggling a class
  const marquees = document.querySelectorAll('.testimonials-marquee, .photos-marquee');
  if (!marquees || marquees.length === 0) return;
  marquees.forEach(function(el){
    el.addEventListener('mouseenter', function(){ el.classList.add('paused'); });
    el.addEventListener('mouseleave', function(){ el.classList.remove('paused'); });
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
