# 🏛️ NexTix Helpdesk API (Enterprise & Government Ready)

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Sistem Manajemen Tiket & Layanan Dukungan IT Terpadu Berstandar ITIL 4, ISO 20000, dan SPBE</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-8.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white" alt="JWT">
  <img src="https://img.shields.io/badge/Compliance-ITIL%20%7C%20SPBE-1E3A8A?style=for-the-badge" alt="Compliance">
</p>

---

## 📑 Daftar Isi
1. [Fitur Utama](#-fitur-utama)
2. [Standarisasi Tata Kelola IT](#-standarisasi-tata-kelola-it)
3. [Panduan Instalasi & Setup](#-panduan-instalasi--setup)
4. [Dokumentasi Lengkap REST API](#-dokumentasi-lengkap-rest-api)
   - [A. Autentikasi (JWT)](#a-autentikasi-jwt)
   - [B. Manajemen Tiket (Tickets)](#b-manajemen-tiket-tickets)
   - [C. Service Level Agreement (SLA Engine)](#c-service-level-agreement-sla-engine)
   - [D. Survei Kepuasan Pengguna (CSAT / IKM)](#d-survei-kepuasan-pengguna-csat--ikm)
   - [E. Berita Acara Perbaikan (BAPP PDF)](#e-berita-acara-perbaikan-bapp-pdf)
   - [F. Multi-Departemen / Unit Kerja OPD](#f-multi-departemen--unit-kerja-opd)
   - [G. Rekapitulasi Laporan & Ekspor](#g-rekapitulasi-laporan--ekspor)
   - [H. Audit Trail Logs (SPBE Compliance)](#h-audit-trail-logs-spbe-compliance)
   - [I. Kategori, Prioritas, Komentar, & FAQ](#i-kategori-prioritas-komentar--faq)
5. [Struktur Role & Hak Akses](#-struktur-role--hak-akses)

---

## 🚀 Fitur Utama

- 🔐 **JWT Token Authentication**: Autentikasi stateless berbasis JSON Web Token dengan kontrol hak akses berbasis Role.
- 🎯 **Server-side Search & Pagination**: Pencarian remote dan paginasi data otomatis untuk performa tinggi pada dataset besar.
- ⏱️ **SLA Auto-Calculation Engine**: Perhitungan target waktu respon (*Response Due*) dan penyelesaian (*Resolution Due*) otomatis berdasarkan matriks prioritas.
- ⭐ **Customer Satisfaction (CSAT / IKM)**: Survei kepuasan 1–5 bintang beserta analitik indeks kepuasan pengguna/masyarakat.
- 📄 **Cetak Berita Acara Perbaikan (BAPP PDF)**: Penerbitan dokumen resmi Berita Acara Penyelesaian Pekerjaan lengkap dengan format kop surat dan kolom tanda tangan para pihak.
- 📊 **Ekspor Laporan Multiformat**: Filter laporan berdasarkan rentang tanggal (*Date Range*), status, kategori, dan ekspor ke **PDF (DomPDF)** serta **Excel XLSX (PhpSpreadsheet)**.
- 🏛️ **Multi-Departemen / OPD**: Pengelompokan tiket dan pengguna berdasarkan unit kerja kedinasan/korporasi.
- 🛡️ **Audit Trail Logging**: Perekaman jejak digital transaksi data untuk kepatuhan audit (SPBE / BPK / Inspektorat).

---

## 🏛️ Standarisasi Tata Kelola IT

| Pilar Standar | Implementasi pada Sistem |
| :--- | :--- |
| **ITIL 4 / ISO 20000** | Klasifikasi tiket menjadi **Incident**, **Service Request**, dan **Change Request**. Perhitungan waktu tanggap dan waktu penyelesaian. |
| **SLA Policy Matrix** | Matriks target waktu per prioritas (*Critical, Block, Major, Normal, Minor, Trivial*) dengan indikator status: `SLA Met`, `Near Breach`, dan `Breached`. |
| **SPBE Compliance** | Rekam jejak audit digital (*Audit Trail*) terhadap seluruh aktivitas pembuatan, perubahan status, rating, dan penghapusan data. |
| **IKM / CSAT** | Indeks kepuasan pengguna sebagai tolok ukur KPI bulanan petugas IT / teknisi. |

---

## ⚙️ Panduan Instalasi & Setup

### 1. Clone & Install Dependencies
```bash
git clone <repository-url>
cd helpdesk-api
composer install
```

### 2. Konfigurasi Environment
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate App Key & JWT Secret
```bash
php artisan key:generate
php artisan jwt:secret
```

### 4. Storage Symlink
```bash
php artisan storage:link
```

### 5. Migrasi & Seed Database
Jalankan migrasi seluruh tabel dan seeder bawaan (Kategori, Role, User Default, FAQ, Prioritas, Departemen, SLA Policy):
```bash
php artisan migrate --seed
```

### 6. Jalankan Local Server
```bash
php artisan serve
```
API berjalan pada `http://127.0.0.1:8000`.

---

## 📘 Dokumentasi Lengkap REST API

> **Catatan Autentikasi**: Seluruh endpoint dengan prefix `/api/auth/*` membutuhkan Header HTTP:
> `Authorization: Bearer <jwt_token>`

---

### A. Autentikasi (JWT)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/auth/register` | Public | Registrasi pengguna baru |
| `POST` | `/api/auth/login` | Public | Login untuk mendapatkan JWT Token |
| `POST` | `/api/auth/refresh` | Auth | Refresh JWT Token yang sedang aktif |
| `POST` | `/api/auth/logout` | Auth | Invalidate JWT Token |

---

### B. Manajemen Tiket (Tickets)

#### 1. List Semua Tiket (Admin & Teknisi)
```http
GET /api/auth/tickets
```
**Query Parameters:**
- `search` *(string)*: Pencarian keyword (nomor tiket, subjek, deskripsi, nama pelapor, unit kerja).
- `page` *(int)*: Nomor halaman paginasi (default: `1`).
- `per_page` *(int)*: Jumlah item per halaman (default: `10`, atau `all` untuk semua data).
- `status` *(string)*: Filter status (`open`, `in_progress`, `closed`).
- `ticket_type` *(string)*: Filter jenis (`incident`, `service_request`, `change_request`).
- `department_id` *(int)*: Filter ID Unit Kerja.
- `priority_id` *(int)*: Filter ID Prioritas.
- `kategori_id` *(int)*: Filter ID Kategori.
- `is_sla_breached` *(boolean)*: Filter kepatuhan SLA (`true` / `false`).

#### 2. List Tiket Milik Pengguna (Client)
```http
GET /api/auth/tickets/user
```
*(Mendukung seluruh query parameter filter dan remote search seperti di atas)*

#### 3. Detail Tiket
```http
GET /api/auth/tickets/{ticket_number}
```

#### 4. Buat Tiket Baru
```http
POST /api/auth/tickets
Content-Type: multipart/form-data
```
**Body (Form Data):**
- `subject` *(string, required)*: Judul/subjek kendala.
- `issue` *(string, required)*: Deskripsi lengkap kendala.
- `priority_id` *(int, required)*: ID Prioritas (SLA akan dihitung otomatis).
- `kategori_id` *(int, required)*: ID Kategori.
- `ticket_type` *(string, optional)*: `incident` | `service_request` | `change_request` (default: `incident`).
- `department_id` *(int, optional)*: ID Unit Kerja/Departemen pelapor.
- `attachment` *(file, optional)*: File lampiran foto/dokumen pendukung (maks. 10MB).

#### 5. Update Status Tiket
```http
PUT /api/auth/tickets/{ticket_number}
```
**Body (JSON):**
```json
{
  "status": "in_progress", // "open" | "in_progress" | "closed"
  "assign_by": "Nama Teknisi" // Opsional
}
```

#### 6. Hapus Tiket & Download Lampiran
- `DELETE /api/auth/tickets/{ticket_number}`: Menghapus tiket dan file lampiran fisiknya.
- `GET /api/auth/tickets/download/{ticket_number}`: Mengunduh file lampiran tiket.

---

### C. Service Level Agreement (SLA Engine)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/auth/sla/policies` | Authenticated | Mengambil daftar target waktu SLA per prioritas |
| `PUT` | `/api/auth/sla/policies/{id}` | Admin (Role 1) | Mengubah target waktu respon (*minutes*) dan resolusi (*minutes*) |
| `GET` | `/api/auth/tickets/sla-stats` | Admin/Support | Ringkasan performa SLA (*Met*, *Breached*, *Near Breach*, *Compliance Rate %*) |

---

### D. Survei Kepuasan Pengguna (CSAT / IKM)

#### 1. Kirim Rating Kepuasan
```http
POST /api/auth/tickets/{ticket_id_or_number}/rate
```
**Body (JSON):**
```json
{
  "rating": 5, // Integer 1 - 5
  "feedback": "Penanganan sangat cepat dan ramah, terima kasih tim IT!"
}
```

#### 2. Laporan Analitik CSAT
```http
GET /api/auth/report/csat
```
Mengembalikan metrik kepuasan: `total_reviews`, `average_score`, `satisfaction_rate_percent`, `distribution` (bintang 1 s/d 5), dan review terbaru.

---

### E. Berita Acara Perbaikan (BAPP PDF)

```http
GET /api/auth/tickets/{ticket_number}/export-bap
```
*Mengunduh file PDF Berita Acara Penyelesaian Pekerjaan (BAPP) resmi standar instansi lengkap dengan nomor surat, rekapan waktu SLA, dan lembar tanda tangan.*

---

### F. Multi-Departemen / Unit Kerja OPD

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/auth/departments` | Authenticated | List semua departemen (dengan search & pagination) |
| `GET` | `/api/auth/departments/active` | Authenticated | List ringkas unit kerja aktif untuk dropdown select |
| `GET` | `/api/auth/departments/{id}` | Authenticated | Detail unit kerja |
| `POST` | `/api/auth/departments` | Admin | Tambah unit kerja baru (`name`, `code`, `description`) |
| `PUT` | `/api/auth/departments/{id}` | Admin | Update unit kerja |
| `DELETE`| `/api/auth/departments/{id}` | Admin | Hapus unit kerja |

---

### G. Rekapitulasi Laporan & Ekspor

| Method | Endpoint | Format | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/auth/report` | JSON | Rekapitulasi data tiket dengan filter & paginasi |
| `GET` | `/api/auth/report/export/pdf` | PDF File | Download laporan dalam format **PDF Landscape** |
| `GET` | `/api/auth/report/export/excel` | XLSX File | Download laporan dalam format **Excel Spreadsheet** |

**Parameter Filter Bersama (Query Params):**
- `start_date`: Tanggal mulai (contoh: `2026-08-01`).
- `end_date`: Tanggal akhir (contoh: `2026-08-31`).
- `status`: Filter status (`open`, `in_progress`, `closed`).
- `category_id` / `kategori_id`: Filter ID Kategori.

---

### H. Audit Trail Logs (SPBE Compliance)

```http
GET /api/auth/audit-logs
```
*(Admin Only)* Menampilkan catatan digital jejak transaksi sistem.
**Filter Params:** `search`, `action`, `entity_type`, `user_id`, `start_date`, `end_date`, `page`, `per_page`.

---

### I. Kategori, Prioritas, Komentar, & FAQ

- **Kategori**: `GET|POST /api/auth/kategoris`, `GET /api/auth/kategoris/active`, `PUT|DELETE /api/auth/kategoris/{id}`
- **Prioritas**: `GET /api/auth/priority`
- **Komentar Diskusi**: `GET /api/auth/comment/{ticket_id}`, `POST /api/auth/comment`, `GET /api/auth/comment/download/{id}`
- **FAQ**: `GET /api/auth/faqs`, `POST|PUT|DELETE /api/auth/faqs/{id}`
- **Statistik Dashboard**: `GET /api/auth/statistics/tickets`, `GET /api/auth/statistics/users`, `GET /api/auth/statistics/usertickets`

---

## 👥 Struktur Role & Hak Akses

| Role ID | Nama Role | Hak Akses Utama |
| :---: | :--- | :--- |
| **1** | **Admin** | Akses penuh seluruh sistem: manajemen user, kelola departemen, konfigurasi SLA, audit trail, laporan rekap & CSAT. |
| **2** | **Support / Teknisi** | Menangani tiket, merubah status tiket, membalas diskusi solusi, memantau dashboard performa SLA. |
| **3** | **Client / Pengguna** | Membuat tiket permohonan/kendala, memantau progress tiket miliknya, berdiskusi pada komentar, memberikan rating kepuasan CSAT, dan mengunduh BAPP. |

---

## 📄 Lisensi
Sistem ini dirilis di bawah lisensi [MIT License](LICENSE).
