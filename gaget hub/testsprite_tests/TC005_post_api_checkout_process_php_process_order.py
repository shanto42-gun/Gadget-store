import requests

BASE_URL = "http://localhost:80/gaget%20hub/index.php"
CHECKOUT_PROCESS_PATH = "/api/checkout/process.php"
LOGIN_PATH = "/pages/login.php"
CART_ADD_PATH = "/api/cart/add.php"

TIMEOUT = 30

def test_post_api_checkout_process_php_process_order():
    session = requests.Session()
    try:
        # First, login a user to get a valid session cookie (using a test user)
        login_payload = {
            "email": "testuser@example.com",
            "password": "TestPass123!"
        }
        login_resp = session.post(
            BASE_URL + LOGIN_PATH,
            json=login_payload,
            timeout=TIMEOUT
        )
        assert login_resp.status_code == 200, f"Login failed with status {login_resp.status_code}"
        assert "PHPSESSID" in session.cookies.get_dict() or login_resp.cookies, "Session cookie missing after login"

        # Setup a product in cart to simulate checkout process
        # Using a dummy product id 1 and quantity 1 - assumed to exist and in stock
        add_cart_payload = {
            "product_id": 1,
            "quantity": 1
        }
        add_cart_resp = session.post(
            BASE_URL + CART_ADD_PATH,
            json=add_cart_payload,
            timeout=TIMEOUT
        )
        assert add_cart_resp.status_code == 200, f"Add to cart failed with status {add_cart_resp.status_code}"

        # Test 1: Successful order processing with all required fields + optional coupon_code
        order_payload = {
            "payment_method": "bKash",
            "shipping_address": "123 Test St, Test City, Country",
            "coupon_code": "DISCOUNT10"
        }
        order_resp = session.post(
            BASE_URL + CHECKOUT_PROCESS_PATH,
            json=order_payload,
            timeout=TIMEOUT
        )
        assert order_resp.status_code == 201, f"Expected 201 Created, got {order_resp.status_code}"
        order_json = order_resp.json()
        assert "order_id" in order_json, "Response missing order_id"
        assert order_json.get("status") == "pending", f"Expected status 'pending', got {order_json.get('status')}"

        # Test 2: Successful order processing without optional coupon_code
        order_payload_no_coupon = {
            "payment_method": "COD",
            "shipping_address": "456 Another St, Another City, Country"
        }
        order_resp_no_coupon = session.post(
            BASE_URL + CHECKOUT_PROCESS_PATH,
            json=order_payload_no_coupon,
            timeout=TIMEOUT
        )
        assert order_resp_no_coupon.status_code == 201, f"Expected 201 Created without coupon, got {order_resp_no_coupon.status_code}"
        order_no_coupon_json = order_resp_no_coupon.json()
        assert "order_id" in order_no_coupon_json, "Response missing order_id without coupon_code"
        assert order_no_coupon_json.get("status") == "pending", f"Expected status 'pending' without coupon, got {order_no_coupon_json.get('status')}"

        # Test 3: Missing required field: shipping_address
        missing_shipping_payload = {
            "payment_method": "bKash"
        }
        missing_shipping_resp = session.post(
            BASE_URL + CHECKOUT_PROCESS_PATH,
            json=missing_shipping_payload,
            timeout=TIMEOUT
        )
        assert missing_shipping_resp.status_code == 400, f"Expected 400 Bad Request for missing shipping_address, got {missing_shipping_resp.status_code}"
        try:
            missing_shipping_json = missing_shipping_resp.json()
        except Exception:
            missing_shipping_json = {}
        assert "error" in missing_shipping_json or "validation" in missing_shipping_json, "Expected error information for missing shipping_address"

        # Test 4: Missing required field: payment_method
        missing_payment_payload = {
            "shipping_address": "789 Missing St, Missing City, Country"
        }
        missing_payment_resp = session.post(
            BASE_URL + CHECKOUT_PROCESS_PATH,
            json=missing_payment_payload,
            timeout=TIMEOUT
        )
        assert missing_payment_resp.status_code == 400, f"Expected 400 Bad Request for missing payment_method, got {missing_payment_resp.status_code}"
        try:
            missing_payment_json = missing_payment_resp.json()
        except Exception:
            missing_payment_json = {}
        assert "error" in missing_payment_json or "validation" in missing_payment_json, "Expected error information for missing payment_method"
    finally:
        session.close()

test_post_api_checkout_process_php_process_order()
