package com.college.portal;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.nio.file.Path;
import java.nio.file.Paths;

public final class DB {
    private static final String URL = "jdbc:mysql://localhost:3306/student_portal?useSSL=false&serverTimezone=UTC";
    private static final String USER = "root";
    private static final String PASSWORD = ""; // Update if your local MySQL has a password.
    // Must point to the same uploads folder used by Q2 PHP.
    public static final Path UPLOAD_DIR = Paths.get("C:/xampp/htdocs/Q2-php/uploads").toAbsolutePath().normalize();
    private DB() {}
    public static Connection getConnection() throws SQLException {
        try { Class.forName("com.mysql.cj.jdbc.Driver"); }
        catch (ClassNotFoundException e) { throw new SQLException("MySQL Connector/J is missing", e); }
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }
}
