import requests

def test_post_pages_login_php_authenticate_user():
    base_url = "http://localhost:80/gaget%20hub/index.php"
    login_url = base_url.rsplit('/', 1)[0] + "/pages/login.php"
    timeout = 30

    # Valid credentials (these should be replaced with real test credentials)
    valid_email = "testuser@example.com"
    valid_password = "correct_password"

    # Invalid credentials
    invalid_email = "testuser@example.com"
    invalid_password = "wrong_password"

    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }

    # Test successful login
    try:
        response = requests.post(
            login_url,
            data={"email": valid_email, "password": valid_password},
            headers=headers,
            timeout=timeout,
            allow_redirects=False
        )
    except requests.RequestException as e:
        assert False, f"Request failed during valid login test: {e}"

    assert response.status_code == 200, f"Expected 200 OK for valid login, got {response.status_code}"
    # Check if a session cookie is set (PHPSESSID or similar)
    cookies = response.cookies
    session_cookie_name = None
    for cookie in cookies:
        if cookie.name.lower().startswith("php") or cookie.name.lower() == "sessionid":
            session_cookie_name = cookie.name
            break
    assert session_cookie_name is not None, "No session cookie set upon valid login"

    # Verify subsequent access with session cookie
    dashboard_url = base_url.rsplit('/', 1)[0] + "/pages/dashboard.php"
    try:
        dash_resp = requests.get(
            dashboard_url,
            cookies={session_cookie_name: cookies.get(session_cookie_name)},
            timeout=timeout,
            allow_redirects=False
        )
    except requests.RequestException as e:
        assert False, f"Request failed during dashboard access with valid session: {e}"

    assert dash_resp.status_code == 200, f"Expected 200 OK for dashboard access with valid session, got {dash_resp.status_code}"

    # Test invalid login
    try:
        invalid_resp = requests.post(
            login_url,
            data={"email": invalid_email, "password": invalid_password},
            headers=headers,
            timeout=timeout,
            allow_redirects=False
        )
    except requests.RequestException as e:
        assert False, f"Request failed during invalid login test: {e}"

    assert invalid_resp.status_code == 401, f"Expected 401 Unauthorized for invalid login, got {invalid_resp.status_code}"
    try:
        json_resp = invalid_resp.json()
    except ValueError:
        assert False, "Invalid login response is not JSON"

    assert "error" in json_resp, "Invalid login response JSON missing 'error' field"
    assert json_resp["error"].lower() == "invalid credentials", f"Expected error 'Invalid credentials', got {json_resp['error']}"

    # Verify dashboard access without session cookie redirects to login page
    try:
        dash_no_cookie_resp = requests.get(
            dashboard_url,
            timeout=timeout,
            allow_redirects=False
        )
    except requests.RequestException as e:
        assert False, f"Request failed during dashboard access without session: {e}"

    assert dash_no_cookie_resp.status_code in (302, 303), f"Expected redirect (302/303) when accessing dashboard without session, got {dash_no_cookie_resp.status_code}"
    location = dash_no_cookie_resp.headers.get("Location", "")
    assert location.endswith("/pages/login.php") or "/pages/login.php" in location, f"Expected redirect location to '/pages/login.php', got '{location}'"

test_post_pages_login_php_authenticate_user()