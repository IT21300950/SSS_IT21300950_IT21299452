import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.svm import OneClassSVM
import joblib

# Sample login data (replace with actual data for better accuracy)
data = {
    'login_time': [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 20, 30],
    'login_attempts': [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 10, 10]
}
df = pd.DataFrame(data)

# Preprocess the data
scaler = StandardScaler()
X_train = scaler.fit_transform(df)

# Train the model
model = OneClassSVM(kernel='rbf', gamma=0.1, nu=0.1)
model.fit(X_train)

# Save the scaler and model
joblib.dump(scaler, 'scaler.pkl')
joblib.dump(model, 'model.pkl')
