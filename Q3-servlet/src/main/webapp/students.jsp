<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %><%@ page import="java.util.*" %>
<%!
private String esc(Object o) { if (o == null) return ""; return String.valueOf(o).replace("&","&amp;").replace("<","&lt;").replace(">","&gt;").replace("\"","&quot;").replace("'","&#x27;"); }
%>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student Profiles</title><link rel="stylesheet" href="style.css"></head><body><header><h1>Student Profiles</h1><a class="logout" href="logout">Log out</a></header><main>
<% List<Map<String,Object>> students = (List<Map<String,Object>>) request.getAttribute("students"); if (students == null || students.isEmpty()) { %><section class="card"><p>No students found. Add records through the PHP admin portal first.</p></section><% } else { for (Map<String,Object> s : students) { %>
<section class="card student"><h2><%= esc(s.get("name")) %></h2><p><strong>Roll number:</strong> <%= esc(s.get("roll")) %></p><p><strong>Email:</strong> <%= esc(s.get("email")) %></p><p><strong>Course:</strong> <%= esc(s.get("course")) %></p><h3>Uploaded files</h3><ul>
<% List<Map<String,Object>> files = (List<Map<String,Object>>) s.get("files"); if (files == null || files.isEmpty()) { %><li>No files uploaded</li><% } else { for (Map<String,Object> f : files) { String type = String.valueOf(f.get("type")); int fid = (Integer) f.get("id"); %><li><a href="file?id=<%= fid %>" target="_blank" rel="noopener"><%= esc(f.get("name")) %></a> — <%= esc(type) %><% if (type.startsWith("image/")) { %><br><img class="preview" src="file?id=<%= fid %>" alt="Uploaded file for <%= esc(s.get("name")) %>"><% } %></li><% } } %></ul></section>
<% } } %></main></body></html>
