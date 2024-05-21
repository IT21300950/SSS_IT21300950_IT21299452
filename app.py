from flask import Flask, request, jsonify
import joblib
import numpy as np

app = Flask(__name__)

# Load the trained model and scaler
scaler = joblib.load('scaler.pkl')
model = joblib.load('model.pkl')

@app.route('/analyze', methods=['POST'])
def analyze():
    data = request.json
    login_time = data.get('login_time')
    login_attempts = data.get('login_attempts')
    
    if login_time is None or login_attempts is None:
        return jsonify({'error': 'Invalid input'}), 400

    # Preprocess the input
    X_new = np.array([[login_time, login_attempts]])
    X_new_scaled = scaler.transform(X_new)

    # Predict if the login attempt is an anomaly
    is_anomaly = model.predict(X_new_scaled)[0] == -1

    return jsonify({'is_anomaly': is_anomaly})

if __name__ == '__main__':
    app.run(port=5001, debug=True)
