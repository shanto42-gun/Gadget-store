import requests
import uuid

BASE_URL = "http://localhost:80/gaget%20hub/index.php"
SIGNUP_PATH = "/pages/signup.php"
TIMEOUT = 30

def test_post_pages_signup_php_register_new_user():
    session = requests.Session()
    headers = {
        "Content-Type": "application/json"
    }

    # Generate unique user info for new registration
    unique_email = f"testuser_{uuid.uuid4()}@example.com"
    new_user_payload = {
        "name": "Test User",
        "email": unique_email,
        "password": "TestPassword123!"
    }

    # 1. Test registration with a new email (expect 201 Created, user_id in response and session cookie)
    try:
        response_new = session.post(
            BASE_URL + SIGNUP_PATH,
            json=new_user_payload,
            headers=headers,
            timeout=TIMEOUT
        )
    except requests.RequestException as e:
        assert False, f"Request to signup new user failed: {e}"

    assert response_new.status_code == 201, f"Expected 201 Created for new user signup, got {response_new.status_code}"
    try:
        json_response = response_new.json()
    except ValueError:
        assert False, "Response for new user signup is not valid JSON"

    assert "user_id" in json_response and isinstance(json_response["user_id"], (int, str)), "Response JSON must contain user_id"
    # Check that session cookie is set
    cookies = session.cookies.get_dict()
    assert any("session" in c.lower() for c in cookies), "Session cookie not found after new user signup"

    # 2. Test registration with existing email (expect 409 Conflict with error message)
    existing_user_payload = {
        "name": "Another User",
        "email": unique_email,  # same email as previous registration
        "password": "AnotherPassword123!"
    }
    try:
        response_existing = requests.post(
            BASE_URL + SIGNUP_PATH,
            json=existing_user_payload,
            headers=headers,
            timeout=TIMEOUT
        )
    except requests.RequestException as e:
        assert False, f"Request to signup existing user failed: {e}"

    assert response_existing.status_code == 409, f"Expected 409 Conflict for duplicate email signup, got {response_existing.status_code}"
    try:
        existing_json = response_existing.json()
    except ValueError:
        assert False, "Response for existing email signup is not valid JSON"

    error_message = existing_json.get("error", "").lower()
    assert "email" in error_message and ("already registered" in error_message or "exists" in error_message), "Expected error message indicating email already registered"

test_post_pages_signup_php_register_new_user()