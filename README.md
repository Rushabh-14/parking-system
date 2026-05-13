
            ██╗  ██╗███████╗██╗     ██╗      ██████╗       ███╗   ██╗██╗ ██████╗███████╗   ████████╗ ██████╗
            ██║  ██║██╔════╝██║     ██║     ██╔═══██╗      ████╗  ██║██║██╔════╝██╔════╝   ╚══██╔══╝██╔═══██╗
            ███████║█████╗  ██║     ██║     ██║   ██║      ██╔██╗ ██║██║██║     █████╗        ██║   ██║   ██║
            ██╔══██║██╔══╝  ██║     ██║     ██║   ██║      ██║╚██╗██║██║██║     ██╔══╝        ██║   ██║   ██║
            ██║  ██║███████╗███████╗███████╗╚██████╔╝▄█╗   ██║ ╚████║██║╚██████╗███████╗      ██║   ╚██████╔╝
            ╚═╝  ╚═╝╚══════╝╚══════╝╚══════╝ ╚═════╝ ╚═╝   ╚═╝  ╚═══╝╚═╝ ╚═════╝╚══════╝      ╚═╝    ╚═════╝  
            
                       ███╗   ███╗███████╗███████╗████████╗   ██╗   ██╗ ██████╗ ██╗   ██╗  ██╗
                       ████╗ ████║██╔════╝██╔════╝╚══██╔══╝   ╚██╗ ██╔╝██╔═══██╗██║   ██║  ██║
                       ██╔████╔██║█████╗  █████╗     ██║       ╚████╔╝ ██║   ██║██║   ██║  ██║
                       ██║╚██╔╝██║██╔══╝  ██╔══╝     ██║        ╚██╔╝  ██║   ██║██║   ██║  
                       ██║ ╚═╝ ██║███████╗███████╗   ██║         ██║   ╚██████╔╝╚██████╔╝  ██║
                       ╚═╝     ╚═╝╚══════╝╚══════╝   ╚═╝         ╚═╝    ╚═════╝  ╚═════╝   ╚═╝

<p align="left"> <img src="https://komarev.com/ghpvc/?username=Rushabh-14&label=Repository%20views&color=0e75b6&style=flat" alt="Rushabh-14" /> </p>
 
<div align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Sedan+SC&size=40&weight=600&duration=5000&pause=700&color=F5F5F5&background=15151500&center=true&vCenter=true&random=false&width=800&lines=Hi%2C+I'm+Rushabh+Parmar+%F0%9F%91%8B;Welcome+to+my+Parking+System+Project!" alt="Typing SVG"/>
</div>

---

## 🚗 Smart Parking Management System  

A complete **QR-based parking management system** built using **PHP, MySQL, HTML, CSS, Bootstrap, and JavaScript**.  
This system efficiently manages **vehicle entries and exits**, **manual ticket generation**, **QR scanning**, and **real-time tracking** of parking data.  

---

## ✨ Features  

✅ **QR Code Based Entry/Exit** — Instant scanning for registered users and one-time tickets for manual entries.  
✅ **Manual Ticket Generator** — Create on-spot parking passes with printable QR tickets.  
✅ **Automatic IN–OUT Switching** — Detects whether a vehicle is entering or leaving.  
✅ **Pass System** — Create, renew, and manage monthly/weekly passes.  
✅ **Dashboard Analytics** — View total entries, two/four-wheeler counts, and daily statistics.  
✅ **Separate Tables for Registered & Manual Users** — Keeps data clean and organized.  
✅ **Admin Access Only** — Secure and managed by administrators only.  
✅ **QR Validation** — Prevents ticket reuse or unauthorized exits.  

---

## 🧠 Tech Stack  

| Layer | Technologies |
|-------|---------------|
| **Frontend** | HTML5, CSS3, Bootstrap, JavaScript |
| **Backend** | PHP (Core PHP) |
| **Database** | MySQL |
| **QR Generation** | PHP QR Code Library |
| **Libraries** | Html5-Qrcode (JS), Font Awesome, Bootstrap Icons |
| **Version Control** | Git & GitHub |

---

## ⚙️ Installation  

1️⃣ **Clone the repository:**  
```bash
git clone https://github.com/Rushabh-14/parking-system.git
```

2️⃣ **Move to project directory:**  
```bash
cd parking-system
```

3️⃣ **Import the SQL file:**  
- Open **phpMyAdmin**
- Create a new database (e.g., `parking`)
- Import the provided SQL file - <a href="https://github.com/user-attachments/files/23289640/parking.sql">parking.sql</a>

4️⃣ **Run the project:**  
Place it inside `htdocs` (for XAMPP) and open in your browser:  
```
http://localhost/parking-system/
```

---

## 🧾 Folder Structure  

```
parking-system/
│
├── dbconn/               # Database connection files
├── phpqrcode/            # PHP QR Code library
├── manual_qr/            # Auto-generated QR tickets
├── admin/                # Admin dashboard and control files
├── viewlot.php           # Parking lot and logs view
├── in-out-logs.php       # Main IN/OUT management file
├── print_ticket.php      # Printable QR ticket format
└── index.php             # Entry point
```

---

## 🧩 Database Overview  

**Tables:**
- `in_out_log` — For registered users’ parking data  
- `manual_in_out` — For manually created ticket logs  
- `register_user` — Stores registered users  
- `parking_type` — Defines vehicle types (two-wheeler, four-wheeler, etc.)  
- `parking_pass` — Monthly or weekly pass data  

---

## 🧑‍💻 Author  

**👋 Rushabh Parmar**  
💼 Developer | 💡 Innovator | 🎯 Passionate about automation and smart systems  

🌐 **Portfolio:** [github.com/Rushabh-14](https://github.com/Rushabh-14)

---

## ⚡ Future Enhancements  

🚀 Add user mobile login for pass holders  
📱 Create mobile app version using React Native  
📊 Add graphical analytics (chart.js) for admin dashboard  
🌐 Integrate online payment for parking passes  

