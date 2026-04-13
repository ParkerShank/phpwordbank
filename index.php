<?php
// ─── Load Quiz Data ───────────────────────────────────────────────
$jsonFile = 'php_quiz.json';
$questions = [];
 
if (file_exists($jsonFile)) {
    $questions = json_decode(file_get_contents($jsonFile), true);
}
 
// Collect all answers for the word bank (shuffled so it's not in order)
$wordBank = array_column($questions, 'answer');
shuffle($wordBank);
 
// ─── Process Submission ───────────────────────────────────────────
$submitted   = false;
$score       = 0;
$results     = [];
$errors      = [];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
 
    foreach ($questions as $index => $q) {
        $key       = 'answer_' . $index;
        $userRaw   = isset($_POST[$key]) ? $_POST[$key] : '';
        $userClean = htmlspecialchars(trim($userRaw), ENT_QUOTES, 'UTF-8');
 
        // Blank field validation
        if ($userClean === '') {
            $errors[] = $index;
        }
 
        $correct   = strtolower(trim($q['answer']));
        $userLower = strtolower($userClean);
        $isCorrect = ($userLower === $correct);
 
        if ($isCorrect) {
            $score++;
        }
 
        $results[] = [
            'question'  => $q['question'],
            'correct'   => $q['answer'],
            'user'      => $userClean,
            'isCorrect' => $isCorrect,
            'blank'     => ($userClean === ''),
        ];
    }
 
    // If any blanks, reset submission so user fixes them
    if (!empty($errors)) {
        $submitted = false;
    }
}
 
$total = count($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PHP Quiz — Word Bank Fill-in-the-Blank</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
</head>
<body>
 
<!-- ░░ HEADER ░░ -->
<header class="site-header">
    <nav class="nav">
        <span class="nav-logo">⟨/⟩ PHP Quiz</span>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="#word-bank">Word Bank</a></li>
            <li><a href="#quiz-form">Questions</a></li>
        </ul>
    </nav>
    <div class="hero">
        <div class="hero-tag">CSCI 4410 · Web Technologies</div>
        <h1 class="hero-title">PHP Word Bank<br><span class="accent">Fill-in-the-Blank</span></h1>
        <p class="hero-sub">Use the word bank to complete each statement about PHP.</p>
    </div>
</header>
 
<main class="container">
 
    <!-- ░░ VALIDATION ERRORS ░░ -->
    <?php if (!empty($errors)): ?>
    <div class="alert-box">
        Please fill in <strong>all blanks</strong> before submitting. 
        <?= count($errors) ?> field(s) were left empty.
    </div>
    <?php endif; ?>
 
    <!-- ░░ WORD BANK ░░ -->
    <section id="word-bank" class="word-bank-section">
        <div class="section-label">Word Bank</div>
        <p class="word-bank-hint">Choose from these words to complete the blanks below:</p>
        <div class="word-bank-grid">
            <?php foreach ($wordBank as $word): ?>
                <span class="word-chip"><?= htmlspecialchars($word, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
    </section>
 
    <!-- ░░ RESULTS VIEW ░░ -->
    <?php if ($submitted): ?>
    <section class="results-section">
        <div class="score-card">
            <div class="score-label">Your Score</div>
            <div class="score-number"><?= $score ?> / <?= $total ?></div>
            <div class="score-pct"><?= round(($score / $total) * 100) ?>%</div>
            <?php
                $pct = ($score / $total) * 100;
                if ($pct === 100)      echo '<div class="score-msg perfect">Perfect score! Outstanding!</div>';
                elseif ($pct >= 70)   echo '<div class="score-msg good">Good job! Keep it up!</div>';
                else                  echo '<div class="score-msg retry">Review the material and try again.</div>';
            ?>
        </div>
 
        <div class="results-list">
            <?php foreach ($results as $i => $r): ?>
            <div class="result-item <?= $r['isCorrect'] ? 'result-correct' : 'result-incorrect' ?>">
                <div class="result-q">
                    <span class="q-num"><?= $i + 1 ?></span>
                    <?= htmlspecialchars($r['question'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="result-answers">
                    <span class="answer-label">Your answer:</span>
                    <span class="user-answer <?= $r['isCorrect'] ? 'ans-correct' : 'ans-incorrect' ?>">
                        <?= $r['blank'] ? '<em>left blank</em>' : htmlspecialchars($r['user'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="verdict <?= $r['isCorrect'] ? 'verdict-correct' : 'verdict-incorrect' ?>">
                        <?= $r['isCorrect'] ? '✓ Correct' : '✗ Incorrect' ?>
                    </span>
                    <?php if (!$r['isCorrect']): ?>
                    <span class="correct-reveal">
                        Correct answer: <strong><?= htmlspecialchars($r['correct'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
 
        <div class="retry-wrap">
            <a href="index.php" class="btn-retry">↩ Restart Quiz</a>
        </div>
    </section>
 
    <!-- ░░ QUIZ FORM VIEW ░░ -->
    <?php else: ?>
    <section id="quiz-form" class="quiz-section">
        <form method="POST" action="index.php">
            <ol class="question-list">
                <?php foreach ($questions as $index => $q): ?>
                <li class="question-item <?= in_array($index, $errors) ? 'has-error' : '' ?>">
                    <div class="question-text">
                        <?= htmlspecialchars($q['question'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="input-wrap">
                        <input
                            type="text"
                            name="answer_<?= $index ?>"
                            class="answer-input"
                            placeholder="Type your answer…"
                            value="<?= isset($_POST['answer_' . $index]) ? htmlspecialchars($_POST['answer_' . $index], ENT_QUOTES, 'UTF-8') : '' ?>"
                            required
                            autocomplete="off"
                        />
                        <?php if (in_array($index, $errors)): ?>
                            <span class="field-error">This field is required.</span>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ol>
 
            <div class="submit-wrap">
                <button type="submit" class="btn-submit">Submit Quiz →</button>
            </div>
        </form>
    </section>
    <?php endif; ?>
 
</main>
 
<!-- ░░ FOOTER ░░ -->
<footer class="site-footer">
    <p>CSCI 4410 · Web Technologies · Parker Shanklin</p>
    <p>MTSU</p>
</footer>
 
</body>
</html>