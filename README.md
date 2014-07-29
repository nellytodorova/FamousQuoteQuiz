FamousQuoteQuiz
===============

General information:
In the famous quote quiz game system will ask questions and user should try to pick a correct answer. Depending on selected mode user will have to choose correct answer from a list of answers, or simply to answer with Yes/No to the question.
Task:
Please create 2 pages:
1)	Main page - shows a famous quote quiz
2)	Settings page – allows switching between modes.
The user has to guess who the author of the quote is.
The application can function in 2 modes:
a)	Binary (Yes/No) – this is the default mode
b)	Multiple choice questions – showing three possible answers, one of which must be the right one. 
Regardless of the currently selected mode, if the user clicks on the right answer a message is displayed “Correct! The right answer is: ….”; if the user clicks on the wrong answer a message is displayed “Sorry, you are wrong! The right answer is: ….”. Then the answer options disappear and the author name is displayed below the quote. Additionally a button ‘Next’ appears below the author name. When the user clicks ‘Next’ they are navigated to the next quote where they can guess the next quote.


Used technologies and instruments:
DBMS – PostgreSQL
Programming language – PHP
IDE – NetBeans

1.	Script for transferring quotes via web service has been developed. It is stored into jobs folder. Returned message is stored into a log file.
2.	If binary mode is selected – one random author from all currently added is selected and shown together with a quote id which are stored into a hidden form fields.
3.	After the request is submitted, depending on the check status (whether we are checking the answer is true or false), the result is evaluated and corresponding message is returned to the user.
4.	If multiple choice mode is selected – the correct author is selected + two more random from the database. After that their order is randomized. If user try to refresh the page, the authors list stays the same. This applies for the binary mode too.
5.	After the last quote is answered, a thank you text is displayed with a total number of correct answers. The user is able to start the quiz once again.
6.	If any of the answers have already been answered and even if the user refresh the page or post answer by modifying the parameters – an error message is displayed.
7.	Changing the quiz mode is placed on separate page, which can be accessed through a menu which is build dynamically by fetching link options from the database. Tables are stored into a separate schema, apart from the one used for the quiz. The functionality can be extended.
8.	The access to the database is performed by separate users/roles with relevant privileges – one for the transfer of the quotes and one for accessing the quiz.
9.	Change of the quiz mode is performed via AJAX requests.
10.	Export/backup of the database is stored into data folder of the supplied project files.
11.	All result statuses are stored into a session.

Some small additions/clarifications:
1.	Authors can be exported into a separate table for better database normalization.
2.	Quiz and change of mode work equal no matter if JavaScript is disabled or not. 
3.	The Cron Job for transferring quotes can be triggered every day as the selected service gives unique quotes on a daily basis and also allows not more than 10 request per day to be performed.
4.	Additional quote number could be added and also how many quotes are left till the end of the quiz.
