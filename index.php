<?php
// Load quiz data
$questions = [];
if (file_exists('php_quiz.json')) {
    $questions = json_decode(file_get_contents('php_quiz.json'), true);
}
 
// Word bank (shuffled)
$wordBank = array_column($questions, 'answer');
shuffle($wordBank);
 
// Process submission
$submitted = false;
$score     = 0;
$results   = [];
$errors    = [];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questions as $index => $q) {
        $key       = 'answer_' . $index;
        $userRaw   = isset($_POST[$key]) ? $_POST[$key] : '';
        $userClean = htmlspecialchars(trim($userRaw), ENT_QUOTES, 'UTF-8');
 
        if ($userClean === '') {
            $errors[] = $index;
        }
 
        $isCorrect = strtolower($userClean) === strtolower(trim($q['answer']));
        if ($isCorrect) $score++;
 
        $results[] = [
            'question'  => $q['question'],
            'correct'   => $q['answer'],
            'user'      => $userClean,
            'isCorrect' => $isCorrect,
            'blank'     => ($userClean === ''),
        ];
    }
 
    if (empty($errors)) {
        $submitted = true;
    }
}
 
$total = count($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PHP Word Bank Quiz</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
 
<h1>PHP Word Bank Fill-in-the-Blank Quiz</h1>
 
<nav>
    <a href="index.php">Home</a>
    <a href="#word-bank">Word Bank</a>
    <a href="#quiz-form">Questions</a>
</nav>
 
<?php if (!empty($errors)): ?>
<div class="alert-box">
    Please fill in <strong>all blanks</strong> before submitting.
    <?= count($errors) ?> field(s) were left empty.
</div>
<?php endif; ?>
 
<!-- Word Bank -->
<div id="word-bank" class="word-bank-section">
    <h2>Word Bank</h2>
    <p>Choose from these words to complete the blanks below:</p>
    <div class="word-bank-grid">
        <?php foreach ($wordBank as $word): ?>
            <span class="word-chip"><?= htmlspecialchars($word, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endforeach; ?>
    </div>
</div>
 
<?php if ($submitted): ?>
<!-- Results -->
<div class="score-card">
    <div class="score-number"><?= $score ?> / <?= $total ?></div>
    <div class="score-msg">
        <?php
            $pct = ($score / $total) * 100;
            if ($pct === 100)    echo 'Perfect score!';
            elseif ($pct >= 70)  echo 'Good job!';
            else                 echo 'Keep studying and try again.';
        ?>
    </div>
</div>
 
<?php foreach ($results as $i => $r): ?>
<div class="result-item <?= $r['isCorrect'] ? 'result-correct' : 'result-incorrect' ?>">
    <strong><?= $i + 1 ?>. <?= htmlspecialchars($r['question'], ENT_QUOTES, 'UTF-8') ?></strong><br>
    Your answer: <strong><?= $r['blank'] ? '<em>left blank</em>' : htmlspecialchars($r['user'], ENT_QUOTES, 'UTF-8') ?></strong>
    <span class="<?= $r['isCorrect'] ? 'verdict-correct' : 'verdict-incorrect' ?>">
        — <?= $r['isCorrect'] ? 'Correct' : 'Incorrect' ?>
    </span>
    <?php if (!$r['isCorrect']): ?>
    <div class="correct-reveal">Correct answer: <strong><?= htmlspecialchars($r['correct'], ENT_QUOTES, 'UTF-8') ?></strong></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
 
<a href="index.php" class="btn-retry">↩ Restart Quiz</a>
 
<?php else: ?>
<!-- Quiz Form -->
<form id="quiz-form" method="POST" action="index.php">
    <?php foreach ($questions as $index => $q): ?>
    <div class="question-item <?= in_array($index, $errors) ? 'has-error' : '' ?>">
        <strong><?= $index + 1 ?>. <?= htmlspecialchars($q['question'], ENT_QUOTES, 'UTF-8') ?></strong>
        <input
            type="text"
            name="answer_<?= $index ?>"
            class="answer-input"
            placeholder="Your answer..."
            value="<?= isset($_POST['answer_' . $index]) ? htmlspecialchars($_POST['answer_' . $index], ENT_QUOTES, 'UTF-8') : '' ?>"
            required
            autocomplete="off"
        />
        <?php if (in_array($index, $errors)): ?>
            <span class="field-error">This field is required.</span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
 
    <button type="submit" class="btn-submit">Submit Quiz →</button>
</form>
<?php endif; ?>
 
<footer>
    <p>CSCI 4410 · Web Technologies · Parker Shanklin</p>
    <p>MTSU</p>
</footer>
 
</body>
</html>