<?php
/**
 * API Test Script
 * Simple test to verify API endpoints are working
 * This file should be removed in production
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

// Only allow in debug mode
if (!DEBUG_MODE) {
    http_response_code(404);
    exit('Not found');
}

header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Test Interface</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .endpoint { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .method { background: #007bff; color: white; padding: 5px 10px; border-radius: 3px; }
        .post { background: #28a745; }
        .get { background: #17a2b8; }
        textarea { width: 100%; height: 100px; }
        button { background: #007bff; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .response { background: #f8f9fa; padding: 10px; margin-top: 10px; border-left: 3px solid #007bff; }
        .error { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <h1>Mobile API Test Interface</h1>
    <p><strong>Note:</strong> This test interface is only available in debug mode.</p>
    
    <div class="endpoint">
        <h3><span class="method post">POST</span> /api/login</h3>
        <p>Test enquirer login and get JWT token</p>
        <textarea id="loginData" placeholder='{"username": "enquirer_username", "password": "password"}'></textarea>
        <br><br>
        <button onclick="testLogin()">Test Login</button>
        <div id="loginResponse" class="response" style="display:none;"></div>
    </div>
    
    <div class="endpoint">
        <h3><span class="method get">GET</span> /api/get_numbers</h3>
        <p>Get assigned phone numbers (requires JWT token from login)</p>
        <input type="text" id="token" placeholder="JWT Token from login response" style="width: 100%; margin-bottom: 10px;">
        <input type="text" id="getParams" placeholder="Query parameters: ?page=1&limit=10&status=new" style="width: 100%;">
        <br><br>
        <button onclick="testGetNumbers()">Test Get Numbers</button>
        <div id="getNumbersResponse" class="response" style="display:none;"></div>
    </div>
    
    <div class="endpoint">
        <h3><span class="method post">POST</span> /api/log_call</h3>
        <p>Log a call (requires JWT token and valid enquiry_id)</p>
        <textarea id="callData" placeholder='{"enquiry_id": 1, "call_outcome": "answered", "call_duration": 120, "notes": "Test call"}'></textarea>
        <br><br>
        <button onclick="testLogCall()">Test Log Call</button>
        <div id="logCallResponse" class="response" style="display:none;"></div>
    </div>

    <script>
        async function testLogin() {
            const data = document.getElementById('loginData').value || '{"username": "test_enquirer", "password": "password123"}';
            
            try {
                const response = await fetch('/api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: data
                });
                
                const result = await response.json();
                const responseDiv = document.getElementById('loginResponse');
                responseDiv.style.display = 'block';
                responseDiv.className = 'response ' + (result.success ? '' : 'error');
                responseDiv.innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                
                if (result.success && result.data.token) {
                    document.getElementById('token').value = result.data.token;
                }
            } catch (error) {
                document.getElementById('loginResponse').innerHTML = '<pre>Error: ' + error.message + '</pre>';
                document.getElementById('loginResponse').style.display = 'block';
                document.getElementById('loginResponse').className = 'response error';
            }
        }
        
        async function testGetNumbers() {
            const token = document.getElementById('token').value;
            const params = document.getElementById('getParams').value || '';
            
            if (!token) {
                alert('Please login first to get a JWT token');
                return;
            }
            
            try {
                const response = await fetch('/api/get_numbers.php' + params, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                    }
                });
                
                const result = await response.json();
                const responseDiv = document.getElementById('getNumbersResponse');
                responseDiv.style.display = 'block';
                responseDiv.className = 'response ' + (result.success ? '' : 'error');
                responseDiv.innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
            } catch (error) {
                document.getElementById('getNumbersResponse').innerHTML = '<pre>Error: ' + error.message + '</pre>';
                document.getElementById('getNumbersResponse').style.display = 'block';
                document.getElementById('getNumbersResponse').className = 'response error';
            }
        }
        
        async function testLogCall() {
            const token = document.getElementById('token').value;
            const data = document.getElementById('callData').value || '{"enquiry_id": 1, "call_outcome": "answered", "call_duration": 120}';
            
            if (!token) {
                alert('Please login first to get a JWT token');
                return;
            }
            
            try {
                const response = await fetch('/api/log_call.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                    },
                    body: data
                });
                
                const result = await response.json();
                const responseDiv = document.getElementById('logCallResponse');
                responseDiv.style.display = 'block';
                responseDiv.className = 'response ' + (result.success ? '' : 'error');
                responseDiv.innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
            } catch (error) {
                document.getElementById('logCallResponse').innerHTML = '<pre>Error: ' + error.message + '</pre>';
                document.getElementById('logCallResponse').style.display = 'block';
                document.getElementById('logCallResponse').className = 'response error';
            }
        }
    </script>
</body>
</html>