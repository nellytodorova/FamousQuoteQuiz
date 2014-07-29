<?php
if (empty($thanksMsg)) {
    echo '<div id="quoteTitle">Who Said it?</div>';
}

$formAction = trim($sessObj->pageDetails['uri']);

if (empty($formAction)) {
    $formAction = 'index.php';
}

if (((int)$quoteId == 0 || empty($quoteText)) && empty($thanksMsg)) {
    throw new Exception('Missing quotes data!');
}
?>
<div id="quoteText"><?= (!empty($thanksMsg))? $thanksMsg : '"' . $quoteText . '"'; ?></div>
<?php
if (!empty($resultMessage)) {
    ?>
    <div id="result" <?= !empty($resultClass) ? 'class="' . $resultClass . '"' : '' ?>>
     <?= !empty($resultMessage) ? $resultMessage : '' ?>
</div>
<div class="clearfix"></div>
<?php
}
?>
<form action="<?= $formAction; ?>" method="post" id="quizForm" name="quizForm">
<?php
echo $this->fetch($sessObj->quizTemplatePath);
?>
</form>