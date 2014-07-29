<?php
if (!empty($thanksMsg)) {
    echo '<input id="startButton" class="startButton" type="submit" value="Start Again" name="startButton" />';
} else {
    if (empty($resultMessage)) {
        if (is_array($authorsAnswers)) {
            foreach ($authorsAnswers as $author) {
                echo '<div><input type="radio" name="author" value="' . $author . '" /><span>' . $author . '</span></div>';
            }
        }
        echo '<input type="submit" name="submitMultiple" value="Submit" class="submitButton" />';
    } else {
        echo '<input id="nextButton" class="nextButtonMultiple" type="submit" value="Next" name="nextButton" />';
    }
}

echo '<input id="quoteId" type="hidden" value="' . (int)$quoteId . '" name="quoteId" />';
?>
<div class="clearfix"></div>