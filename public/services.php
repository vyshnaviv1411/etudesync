<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header_public.php';
?>

<section class="page-hero" aria-labelledby="services-title">
  <h1 id="services-title">Services</h1>
  <p class="lead">ÉtudeSync provides tools to help students, teachers and professionals collaborate, learn, and stay focused — all from one tidy study desk.</p>

  <ul class="services-grid" role="list">

    <!-- CollabSphere (Rooms) - FREE -->
    <li class="service-card glass-card">
      <div class="card-inner">
        <div class="card-left">
          <h3 class="service-title">CollabSphere (Rooms)</h3>
          <ul class="service-points">
            <li>Create / list / join scheduled rooms (room code)</li>
            <li>Text-centric chat + file sharing (PDFs, images)</li>
            <li>Simple canvas whiteboard (save as PNG)</li>
            <li>Quick polls for host-driven decisions</li>
          </ul>
          <blockquote class="testimonial">“We finished our revision in 30 minutes — the room kept everyone focused.” — <strong>Priya, Student</strong></blockquote>
        </div>

        <div class="card-right">
          <img src="assets/images/icon-collabsphere.png" alt="CollabSphere icon" class="service-icon" />
        </div>
      </div>
    </li>

    <!-- FocusFlow (Productivity Zone) - FREE -->
    <li class="service-card glass-card">
      <div class="card-inner">
        <div class="card-left">
          <h3 class="service-title">FocusFlow (Productivity Zone)</h3>
          <ul class="service-points">
            <li>Pomodoro timer with persistence</li>
            <li>DB-backed to-do list + due dates</li>
            <li>Simple calendar & weekly study planner</li>
            <li>Progress tracker charts for motivation</li>
          </ul>
          <blockquote class="testimonial">“Pomodoro helped me get into flow — my focus improved.” — <strong>Rohit, Developer</strong></blockquote>
        </div>

        <div class="card-right">
          <img src="assets/images/icon-focusflow.png" alt="FocusFlow icon" class="service-icon" />
        </div>
      </div>
    </li>

<!-- MindPlay (Journal + Games) - FREE (single-user only) -->
<li class="service-card glass-card">
  <div class="card-inner">
    <div class="card-left">
      <h3 class="service-title">MindPlay (Personal Journal & Games)</h3>
      <ul class="service-points">
        <li>Private journal (write, search, export entries)</li>
        <li>Mini single-player games with local scoring and server leaderboards</li>
        <li>Personal badges to reward progress (single-user achievements)</li>
      </ul>
      <blockquote class="testimonial">“I used the journal and the quick games between study sessions — it helped me reset.” — <strong>Vikram, Student</strong></blockquote>
    </div>

    <div class="card-right">
      <img src="assets/images/icon-mindplay.png" alt="MindPlay icon" class="service-icon" />
    </div>
  </div>
</li>


    <!-- --- PREMIUM FEATURES BELOW: keep them lower in the list --- -->
<!-- QuizForge (Quizzes & Live Rooms) - PREMIUM
     Anyone can create a quiz and publish a room code; participants join the quiz-room,
     play in real-time, receive automatic evaluation and per-user reports. -->
<li class="service-card glass-card premium-card">
  <div class="card-inner">
    <div class="card-left">
      <h3 class="service-title">AssessArena (Quizzes & Live Rooms) <span class="premium-tag" aria-hidden="true">Premium</span></h3>
      <ul class="service-points">
        <li>Anyone can create a quiz and publish a Quiz Room code</li>
        <li>Participants join the quiz room, play in real-time (synchronized questions)</li>
        <li>Automatic evaluation during/after play and individual reports</li>
        <li>Result history, per-quiz leaderboard and exportable reports</li>
      </ul>
      <blockquote class="testimonial">“We joined a QuizForge room and got instant reports — very useful for quick practice.” — <strong>Amy, Learner</strong></blockquote>
    </div>

    <div class="card-right">
      <img src="assets/images/icon-quizforge.png" alt="QuizForge icon" class="service-icon" />
    </div>
  </div>

  <div class="premium-badge" aria-hidden="true">🔒 Premium</div>
</li>


    <!-- InfoVault (Knowledge Hub) - PREMIUM -->
    <li class="service-card glass-card premium-card">
      <div class="card-inner">
        <div class="card-left">
          <h3 class="service-title">InfoVault (Knowledge Hub) <span class="premium-tag" aria-hidden="true">Premium</span></h3>
          <ul class="service-points">
            <li>Upload/download PDFs & images with tags</li>
            <li>Organize with folders or tags & favorites</li>
            <li>Create flashcards & review mode (flip, mark)</li>
            <li>Fast search by tag / title</li>
            <li>Mindmap maker (visual node maps, drag & connect, export PNG/SVG)</li>
          </ul>
          <blockquote class="testimonial">“All my notes in one place — searching is instant.” — <strong>Dr. Iyer, Tutor</strong></blockquote>
        </div>

        <div class="card-right">
          <img src="assets/images/icon-infovault.png" alt="InfoVault icon" class="service-icon" />
        </div>
      </div>

      <div class="premium-badge" aria-hidden="true">🔒 Premium</div>
    </li>

  </ul>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
