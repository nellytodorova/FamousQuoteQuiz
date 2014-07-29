<?php
if (empty($resultMessage) && empty($thanksMsg)) {
    echo '<div id="author">' . $authorsAnswers[0] . '</div>';
}
?>
<div id="buttons">
<?php
if (!empty($thanksMsg)) {
    echo '<input id="startButton" class="startButton" type="submit" value="Start Again" name="startButton" />';
} else {
    if (empty($resultMessage)) {
        echo '<input id="yesButton" class="yesButton" type="submit" value="Yes" name="yesButton" />';
        echo '<input id="noButton" class="noButton" type="submit" value="No" name="noButton" />';
    } else {
        echo '<input id="nextButton" class="nextButton" type="submit" value="Next" name="nextButton" />';
    }

    echo '<input id="author" type="hidden" value="' . $authorsAnswers[0] . '" name="author" />';
}
?>
</div>
<?php
echo '<input id="quoteId" type="hidden" value="' . (int)$quoteId . '" name="quoteId" />';
?>