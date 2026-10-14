# Web Technology Assignment 2 — Student Portal

This practice project covers all three questions in the assignment: (1) HTML/CSS/JavaScript registration validation, (2) PHP + MySQL CRUD and file uploads, and (3) Java Servlet + JDBC + JSP student listing/file streaming/session access.

## Software
- VS Code
- XAMPP (Apache + PHP + MariaDB/MySQL + phpMyAdmin)
- JDK 11 or later
- Apache Tomcat 10.1 (uses `jakarta.servlet` packages)
- MySQL Connector/J JAR

Official downloads: https://www.apachefriends.org/download.html ; https://tomcat.apache.org/download-10.cgi ; https://dev.mysql.com/downloads/connector/j/

## Database setup (shared by Q2 and Q3)
1. Start Apache and MySQL in XAMPP Control Panel.
2. Open http://localhost/phpmyadmin
3. Select **Import**, choose `Q2-php/database.sql`, and click **Import**. This creates `student_portal` and its tables.
4. The example assumes local database user `root` with an empty password. If your MySQL credentials differ, update `Q2-php/db.php` and `Q3-servlet/src/main/java/com/college/portal/DB.java`.

## Question 1 — registration form
1. Open `Q1-registration` in VS Code.
2. Open `index.html` directly in a browser, or use VS Code Live Server.
3. Try blank fields, an invalid email, a weak password, and a valid submission. The page demonstrates client-side validation only; it does not save to a server/database.

## Question 2 — PHP CRUD and uploads
1. Copy the entire `Q2-php` folder into `C:\xampp\htdocs\Q2-php`.
2. Ensure `C:\xampp\htdocs\Q2-php\uploads` exists and is writable by Apache.
3. Import `database.sql` as described above.
4. Visit http://localhost/Q2-php/ in your browser.
5. Add a student, upload an image/document, view/edit/delete records, and inspect `students` and `student_files` tables in phpMyAdmin.

## Question 3 — Servlet/JDBC/JSP
1. Install JDK 11+ and Tomcat 10.1. This project uses Jakarta Servlet (`jakarta.servlet.*`).
2. Install Maven if it is not already installed, then open a terminal in `Q3-servlet` and run `mvn clean package`. The `pom.xml` downloads MySQL Connector/J automatically.
3. Copy the `Q2-php/uploads` folder contents to a shared folder accessible to the Servlet app, or update `DB.UPLOAD_DIR` in `DB.java` to the absolute path of the PHP upload folder. Example for XAMPP: `C:/xampp/htdocs/Q2-php/uploads/`.
4. Copy `Q3-servlet/target/student-portal.war` into Tomcat’s `webapps` folder and start Tomcat. Alternatively import the project as a Maven web project in Eclipse and run it on Tomcat.
5. Open `http://localhost:8080/student-portal/login.jsp`.
6. Demo login: username `student`, password `Student@123`. This demo credential is hard-coded in `LoginServlet`; replace it with database-backed authentication before real deployment.
7. The app lists students from the same database and streams files through `FileServlet` after checking the session.

## Important notes
- Question 1 is a front-end validation demonstration. Questions 2 and 3 share a database schema.
- Upload checks in Q2 use extension, detected MIME type, size limit, random stored names, and prepared statements. For production, add authentication/authorization, CSRF protection, antivirus scanning, and store uploads outside the public web root.
- If Apache/MySQL ports are busy, stop the conflicting service or change XAMPP ports.
- Submission checklist: source files, SQL schema, screenshots of working output, inline comments, 3–5 line conclusion, and GitHub repository link.
