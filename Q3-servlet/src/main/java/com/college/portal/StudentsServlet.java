package com.college.portal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

@WebServlet("/students")
public class StudentsServlet extends HttpServlet {
    @Override protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("loggedInUser") == null) {
            response.sendRedirect(request.getContextPath() + "/login.jsp"); return;
        }
        List<Map<String,Object>> students = new ArrayList<>();
        String sql = "SELECT s.id,s.name,s.roll_number,s.email,s.course,f.id AS file_id,f.original_name,f.file_type " +
                     "FROM students s LEFT JOIN student_files f ON s.id=f.student_id ORDER BY s.id DESC,f.id DESC";
        try (Connection con = DB.getConnection(); PreparedStatement ps = con.prepareStatement(sql); ResultSet rs = ps.executeQuery()) {
            Map<Integer, Map<String,Object>> byId = new LinkedHashMap<>();
            while (rs.next()) {
                int id = rs.getInt("id");
                Map<String,Object> student = byId.get(id);
                if (student == null) {
                    student = new LinkedHashMap<>(); student.put("id", id); student.put("name", rs.getString("name"));
                    student.put("roll", rs.getString("roll_number")); student.put("email", rs.getString("email"));
                    student.put("course", rs.getString("course")); student.put("files", new ArrayList<Map<String,Object>>());
                    byId.put(id, student);
                }
                int fileId = rs.getInt("file_id");
                if (!rs.wasNull()) {
                    @SuppressWarnings("unchecked") List<Map<String,Object>> files = (List<Map<String,Object>>) student.get("files");
                    Map<String,Object> file = new LinkedHashMap<>(); file.put("id", fileId); file.put("name", rs.getString("original_name"));
                    file.put("type", rs.getString("file_type")); files.add(file);
                }
            }
            students.addAll(byId.values());
        } catch (SQLException e) {
            throw new ServletException("Could not retrieve students. Check database configuration.", e);
        }
        request.setAttribute("students", students);
        request.getRequestDispatcher("/students.jsp").forward(request, response);
    }
}
