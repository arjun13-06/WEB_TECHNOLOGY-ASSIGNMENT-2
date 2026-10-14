package com.college.portal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    @Override protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String username = request.getParameter("username");
        String password = request.getParameter("password");
        // Demonstration-only credentials. Replace with database-backed password-hash verification in production.
        if ("student".equals(username) && "Student@123".equals(password)) {
            HttpSession old = request.getSession(false);
            if (old != null) old.invalidate(); // avoid session fixation
            HttpSession session = request.getSession(true);
            session.setAttribute("loggedInUser", username);
            session.setMaxInactiveInterval(20 * 60);
            response.sendRedirect(request.getContextPath() + "/students");
        } else {
            request.setAttribute("error", "Invalid username or password.");
            request.getRequestDispatcher("/login.jsp").forward(request, response);
        }
    }
}
