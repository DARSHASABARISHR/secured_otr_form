SECURED (OTR) FORM — SETUP
1. Extract the ZIP.
2. Copy folder secured_otr_form to C:\xampp\htdocs\
3. Start Apache and MySQL in XAMPP.
4. Open http://localhost/phpmyadmin and import database/schema.sql.
5. In config/config.php, enter your Gmail address and Gmail App Password for email OTP. Do not use your normal Gmail password and do not share the App Password.
6. Open http://localhost/secured_otr_form/

Stack: HTML/CSS/JavaScript frontend, PHP backend, MySQL database.
Phone OTP: DEMO ONLY. A 4-digit OTP appears on screen, expires after 60 seconds, and resend unlocks after 60 seconds. No SMS is sent; this does not prove phone ownership.
Email OTP: expires after 5 minutes.
This is a local classroom project, not production-ready authentication.
