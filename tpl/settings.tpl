<div id="quoteTitle">Please select a mode of the quiz:</div>
<?php
$formAction = trim($sessObj->pageDetails['uri']);

if (empty($formAction)) {
    $formAction = 'index.php';
}
?>
<form action="<?= $formAction; ?>" method="post" id="settingsForm" name="settingsForm">
    <div><input type="radio" name="changeGroup" value="binary" <?= ($sessObj->quizMode == 'multiple') ? 'checked' : ''; ?> /><span>Binary (Yes/No)</span></div>
    <div><input type="radio" name="changeGroup" value="multiple" <?= ($sessObj->quizMode == 'binary') ? 'checked' : ''; ?> /><span>Multiple choice questions</span></div>

    <input type="submit" name="changeMode" value="Choose Mode" class="changeModeButton" />
</form>
<div class="clearfix"></div>
<div id="result" <?= !empty($resultClass) ? 'class="' . $resultClass . '"' : '' ?>>
     <?= !empty($resultMessage) ? $resultMessage : '' ?>
</div>