import requests

BASE_URL = "http://localhost:80/gaget%20hub/index.php"
ADMIN_LOGIN_PATH = "/admin/login.php"
ADMIN_PRODUCTS_PATH = "/admin/products.php"

ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "adminpassword"

def test_get_admin_products_php_admin_product_management():
    session = requests.Session()
    login_url = BASE_URL.rsplit("/", 1)[0] + ADMIN_LOGIN_PATH
    admin_products_url = BASE_URL.rsplit("/", 1)[0] + ADMIN_PRODUCTS_PATH

    # Attempt to login to get admin session cookie
    login_payload = {
        "email": ADMIN_EMAIL,
        "password": ADMIN_PASSWORD
    }
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }
    # Login as admin to get session cookie
    response_login = session.post(login_url, data=login_payload, headers=headers, timeout=30)
    # Check login success, expect 200 OK and session cookie
    assert response_login.status_code == 200, f"Admin login failed: {response_login.status_code} - {response_login.text}"
    assert response_login.cookies, "No cookies received after admin login"

    # Access admin products page with valid admin session cookie
    response_auth = session.get(admin_products_url, timeout=30, allow_redirects=False)
    assert response_auth.status_code == 200, f"Authorized admin access to products page failed: {response_auth.status_code}"
    # Basic check for product management UI presence (e.g. presence of "product list" or "Manage Products" in body)
    assert ("product" in response_auth.text.lower() or "manage" in response_auth.text.lower()), "Product management UI text not found in response for authorized admin"

    # Access admin products page without session cookie to simulate unauthorized access
    # Using a fresh session without cookies
    session_no_auth = requests.Session()
    response_unauth = session_no_auth.get(admin_products_url, timeout=30, allow_redirects=False)
    # Expect either 302 Redirect to /admin/login.php or 401 Unauthorized
    assert response_unauth.status_code in (302, 401), f"Unauthorized access expected 302 or 401 but got {response_unauth.status_code}"
    if response_unauth.status_code == 302:
        location = response_unauth.headers.get("Location", "")
        assert location.endswith("/admin/login.php"), f"Redirect location unexpected: {location}"

test_get_admin_products_php_admin_product_management()