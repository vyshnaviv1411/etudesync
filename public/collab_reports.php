<?php
// public/reports.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/reports.php';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid = (int) $_SESSION['user_id'];

/* -------------------------
   DATA QUERIES
-------------------------- */

// Rooms created
$stmtCreated = $pdo->prepare("
    SELECT COUNT(*) 
    FROM rooms 
    WHERE host_user_id = :uid
");
$stmtCreated->execute(['uid' => $uid]);
$totalCreated = (int) $stmtCreated->fetchColumn();

// Rooms joined (exclude own rooms)
$stmtJoined = $pdo->prepare("
    SELECT COUNT(DISTINCT rp.room_id)
    FROM room_participants rp
    INNER JOIN rooms r ON r.room_id = rp.room_id
    WHERE rp.user_id = :uid
      AND r.host_user_id <> :uid
");
$stmtJoined->execute(['uid' => $uid]);
$totalJoined = (int) $stmtJoined->fetchColumn();

// Total participation
$totalParticipation = $totalCreated + $totalJoined;

// Recent activity
$stmtRecent = $pdo->prepare("
    SELECT r.title, rp.joined_at
    FROM room_participants rp
    INNER JOIN rooms r ON r.room_id = rp.room_id
    WHERE rp.user_id = :uid
    ORDER BY rp.joined_at DESC
    LIMIT 1
");
$stmtRecent->execute(['uid' => $uid]);
$recentActivity = $stmtRecent->fetch(PDO::FETCH_ASSOC);

// Analysis
if ($totalCreated > $totalJoined) {
    $collabStyle = 'Room Creator';
    $styleMsg = 'You prefer initiating collaboration by creating study rooms.';
} elseif ($totalJoined > $totalCreated) {
    $collabStyle = 'Active Participant';
    $styleMsg = 'You prefer joining existing rooms and collaborating with others.';
} else {
    $collabStyle = 'Balanced Collaborator';
    $styleMsg = 'You maintain a balance between creating and joining rooms.';
}

// Engagement
if ($totalParticipation === 0) {
    $engagement = 'Low';
} elseif ($totalParticipation <= 3) {
    $engagement = 'Moderate';
} else {
    $engagement = 'High';
}

// Graph scaling (avoid divide-by-zero)
$maxValue = max(1, $totalCreated, $totalJoined);
$createdPercent = round(($totalCreated / $maxValue) * 100);
$joinedPercent  = round(($totalJoined / $maxValue) * 100);
?>

<link rel="stylesheet" href="assets/css/collab.css?v=2">
<script>
    document.body.classList.add('dashboard-page');
</script>

<div class="dashboard-bg" aria-hidden="true"
     style="background-image:url('assets/images/collabsbg.jpg');
            background-size:cover;
            background-position:center;">
    <div class="dashboard-bg-overlay"></div>
</div>

<div class="collab-viewport">
    <div class="collab-hero">
        <div class="collab-card" style="max-width:960px;margin:0 auto;">

            <!-- HEADER -->
            <div class="collab-card-head" style="align-items:center;gap:16px;">
                <img src="assets/images/report-icon.png"
                     class="collab-logo"
                     style="width:64px;height:64px;border-radius:12px;"
                     alt="Reports">
                <div>
                    <h1 style="margin:0;">Activity Report</h1>
                    <p class="lead" style="margin-top:6px;">
                        A visual summary of your CollabSphere collaboration activity.
                    </p>
                </div>
            </div>

            <!-- METRICS -->
            <div class="card-grid"
                 style="margin-top:24px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">

                <div class="card glass-card">
                    <h3>Total Rooms Created</h3>
                    <p class="metric"><?= $totalCreated ?></p>
                </div>

                <div class="card glass-card">
                    <h3>Total Rooms Joined</h3>
                    <p class="metric"><?= $totalJoined ?></p>
                </div>

                <div class="card glass-card">
                    <h3>Total Participation</h3>
                    <p class="metric"><?= $totalParticipation ?></p>
                </div>
            </div>

            <!-- VISUAL COMPARISON -->
            <div class="glass-card" style="margin-top:26px;padding:20px;">
                <h3 style="margin-bottom:14px;">Room Activity Comparison</h3>

                <div class="progress-block">
                    <div class="progress-label">
                        <span>Rooms Created</span>
                        <strong><?= $totalCreated ?></strong>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill created"
                             style="width:<?= $createdPercent ?>%"></div>
                    </div>
                </div>

                <div class="progress-block">
                    <div class="progress-label">
                        <span>Rooms Joined</span>
                        <strong><?= $totalJoined ?></strong>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill joined"
                             style="width:<?= $joinedPercent ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- INSIGHTS -->
            <section style="margin-top:28px;">
                <h2 class="section-title">Insights</h2>

                <div class="card-grid"
                     style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">

                    <div class="card glass-card">
                        <h4>📌 Collaboration Style</h4>
                        <p class="highlight"><?= htmlspecialchars($collabStyle) ?></p>
                        <p class="muted"><?= htmlspecialchars($styleMsg) ?></p>
                    </div>

                    <div class="card glass-card">
                        <h4>📊 Engagement Level</h4>
                        <p class="highlight"><?= $engagement ?></p>
                        <p class="muted">Based on your collaboration frequency.</p>
                    </div>

                    <div class="card glass-card">
                        <h4>🕒 Recent Activity</h4>
                        <?php if ($recentActivity): ?>
                            <p class="highlight">
                                <?= htmlspecialchars($recentActivity['title']) ?>
                            </p>
                            <p class="muted">
                                <?= date('d M Y, H:i', strtotime($recentActivity['joined_at'])) ?>
                            </p>
                        <?php else: ?>
                            <p class="muted">No activity yet</p>
                        <?php endif; ?>
                    </div>

                </div>
            </section>

            <!-- BACK BUTTON -->
            <div style="margin-top:22px;">
                <a href="collabsphere.php" class="btn primary">
                    ← Back to Modules
                </a>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
