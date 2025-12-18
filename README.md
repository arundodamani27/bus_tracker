# 🚌 Bus Tracker System

A web-based Bus Tracker System that helps passengers find buses based on **From Stop, To Stop, and Current Time**, and allows drivers to manage trips and route stops efficiently.

This project focuses on solving real-world public transport problems using a simple and scalable web application.

---

## 🚀 Features

### 👤 Passenger Module
- Search buses using **From Stop**, **To Stop**, and **Current Time**
- View **exact arrival time** of the bus at the selected stop
- See only **valid buses** (bus is shown only if it has not passed the stop)

### 🚍 Driver / Conductor Module
- Add and manage bus trips
- Define trip start and end points with timings
- Add route stops with arrival times
- View passenger-added stops
- Delete stops when required
- Update trip status (Inactive / Scheduled / Running Now)

---

## 🛠 Tech Stack

- **Frontend:** HTML, CSS, Bootstrap, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  
- **Server:** Apache (XAMPP / InfinityFree Hosting)

---

## 📁 Project Structure
BusTracker/
├── index.php                # Main passenger bus search page
├── tracker.php              # Bus search logic (stop & time based)
├── manage_trip.php          # Driver: add/edit trips and route stops
├── my_trips.php             # Driver: view all assigned trips
├── add_stop_passenger.php   # Passenger: request a new stop
├── delete_stop.php          # Delete a stop from a trip
├── login.php                # Driver login
├── logout.php               # Driver logout
├── dashboard.php            # Driver dashboard
├── db_connect.php           # Database connection configuration
├── assets/
│   ├── css/                 # Custom stylesheets
│   ├── js/                  # JavaScript files
│   └── images/              # Images/icons
└── README.md                # Project documentation



---

## 🗄 Database Design

### `trips` Table
Stores trip-level information added by drivers.

- trip_id
- driver_id
- route_number
- bus_name
- start_location
- end_location
- start_time
- end_time
- status

### `stops` Table
Stores all route stops added by drivers and passengers.

- stop_id
- trip_id
- stop_name
- stop_time
- added_by (`driver` / `passenger`)

### `drivers` Table
Stores driver authentication details.

- driver_id
- name
- phone
- password

---

## 🔍 How Bus Search Works

1. Passenger enters **From Stop**, **To Stop**, and **Current Time**
2. System checks:
   - Both stops exist in the same trip
   - Bus has **not yet passed** the boarding stop
   - Boarding stop comes **before** destination stop
3. Valid buses are displayed with:
   - Bus name
   - Route number
   - Arrival time at boarding stop
   - Trip status

---

## ⚙️ How to Run the Project (Local)

1. Install **XAMPP**
2. Start **Apache** and **MySQL**
3. Create a database and import tables
4. Update database credentials in `db_connect.php`
5. Place project folder inside:
6. Open browser and visit: http://localhost/BusTracker

---

## 🌐 Live Hosting
This project can be hosted on **InfinityFree** or any PHP-supported hosting platform.

---

## 🎯 Future Enhancements

- Live GPS tracking using driver mobile GPS
- Google Maps integration
- Real-time notifications
- Passenger seat availability
- Admin dashboard

---

## 👨‍💻 Author

**Arun**  
MCA Student | Web Developer  
Interested in Full Stack Development and Problem Solving

---

## 📜 License

This project is licensed under the **MIT License**.

