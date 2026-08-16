import requests

BASE_URL = "http://localhost:80/gaget%20hub/index.php"
ADMIN_LOGIN_URL = BASE_URL.rsplit('/', 1)[0] + "/admin/login.php"
ADMIN_ORDERS_URL = BASE_URL.rsplit('/', 1)[0] + "/admin/orders.php"
TIMEOUT = 30

def test_get_admin_orders_php_admin_order_management():
    admin_email = "admin@example.com"
    admin_password = "adminpassword"  # Replace with valid admin credentials for actual test

    session = requests.Session()
    try:
        # Login as admin to get session cookie
        login_payload = {
            "email": admin_email,
            "password": admin_password
        }
        login_response = session.post(ADMIN_LOGIN_URL, data=login_payload, timeout=TIMEOUT)
        assert login_response.status_code == 200, f"Admin login failed with status {login_response.status_code}"
        # Confirm login cookie is set
        assert session.cookies, "No cookies received after admin login"

        # Request admin orders page with session cookie
        orders_response = session.get(ADMIN_ORDERS_URL, timeout=TIMEOUT)
        assert orders_response.status_code == 200, f"Expected 200 OK but got {orders_response.status_code}"
        # Check for typical admin order management UI content in response text
        response_text = orders_response.text.lower()
        assert "order" in response_text or "orders" in response_text or "management" in response_text, "Order management UI content not found in response"

    finally:
        session.close()

test_get_admin_orders_php_admin_order_management()