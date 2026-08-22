<?php

namespace Database\Seeders;

use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach ($this->programs() as $row) {
            StudyProgram::query()->updateOrCreate(
                ['id' => $row['id']],
                [
                    'department_id' => $row['department_id'],
                    'name' => $row['name'],
                    'id_prodi_gerbang' => $row['id_prodi_gerbang'],
                    'study_program_type_id' => $row['study_program_type_id'],
                    'created_at' => $row['created_at'] ?? $now,
                    'updated_at' => $row['updated_at'] ?? $now,
                ]
            );
        }
    }

    private function programs(): array
    {
        return [
            ['id' => 1, 'department_id' => 4, 'name' => ['id' => 'Arsitektur', 'en' => 'Architecture'], 'id_prodi_gerbang' => '44115', 'study_program_type_id' => 1],
            ['id' => 2, 'department_id' => 2, 'name' => ['id' => 'Bisnis Digital', 'en' => 'Digital Business'], 'id_prodi_gerbang' => '11120', 'study_program_type_id' => 1],
            ['id' => 3, 'department_id' => 4, 'name' => ['id' => 'Desain Komunikasi Visual', 'en' => 'Visual Communication Design'], 'id_prodi_gerbang' => '44122', 'study_program_type_id' => 1],
            ['id' => 4, 'department_id' => 1, 'name' => ['id' => 'Fisika', 'en' => 'Physics'], 'id_prodi_gerbang' => '22101', 'study_program_type_id' => 1],
            ['id' => 5, 'department_id' => 1, 'name' => ['id' => 'Ilmu Aktuaria', 'en' => 'Actuarial Science '], 'id_prodi_gerbang' => '11117', 'study_program_type_id' => 1],
            ['id' => 6, 'department_id' => null, 'name' => ['id' => 'Inbound Lintas Prodi', 'en' => 'Inter Field of Study (FoS) Student Exchange Program'], 'id_prodi_gerbang' => '99196', 'study_program_type_id' => 1],
            ['id' => 7, 'department_id' => null, 'name' => ['id' => 'Inbound Permata Merdeka', 'en' => 'Permata Merdeka Student Exchange Program'], 'id_prodi_gerbang' => '99197', 'study_program_type_id' => 1],
            ['id' => 8, 'department_id' => null, 'name' => ['id' => 'Inbound Permata Sakti', 'en' => 'Permata Sakti Inbound'], 'id_prodi_gerbang' => '99199', 'study_program_type_id' => 1],
            ['id' => 9, 'department_id' => null, 'name' => ['id' => 'Inbound Pertukaran Mahasiswa Merdeka-DN', 'en' => 'PMMDN Student Exchange Program'], 'id_prodi_gerbang' => '99195', 'study_program_type_id' => 1],
            ['id' => 10, 'department_id' => 2, 'name' => ['id' => 'Informatika', 'en' => 'Informatics'], 'id_prodi_gerbang' => '11111', 'study_program_type_id' => 1],
            ['id' => 11, 'department_id' => null, 'name' => ['id' => 'International Mobility', 'en' => 'International Mobility'], 'id_prodi_gerbang' => '88184', 'study_program_type_id' => 1],
            ['id' => 12, 'department_id' => null, 'name' => ['id' => 'Kredensial Mikro Mahasiswa Indonesia-KMMI', 'en' => 'Indonesian Student Micro Credentials'], 'id_prodi_gerbang' => '99192', 'study_program_type_id' => 1],
            ['id' => 13, 'department_id' => 1, 'name' => ['id' => 'Matematika', 'en' => 'Mathematics'], 'id_prodi_gerbang' => '11102', 'study_program_type_id' => 1],
            ['id' => 14, 'department_id' => null, 'name' => ['id' => 'Outbond Indonesia International Student Mobility Awards', 'en' => 'Outbond Indonesia International Student Mobility Awards'], 'id_prodi_gerbang' => '88187', 'study_program_type_id' => 1],
            ['id' => 15, 'department_id' => null, 'name' => ['id' => 'Outbond Inisiasi Prodi', 'en' => 'Study Program Initiation Outbound'], 'id_prodi_gerbang' => '99188', 'study_program_type_id' => 1],
            ['id' => 16, 'department_id' => null, 'name' => ['id' => 'Outbound Merdeka Belajar Indonesia Cyber Education', 'en' => 'Outbound Freedom of Learning Program of Indonesia Cyber Education'], 'id_prodi_gerbang' => '99190', 'study_program_type_id' => 1],
            ['id' => 17, 'department_id' => null, 'name' => ['id' => 'Outbound Merdeka Belajar Indonesia Cyber Education', 'en' => 'Outbound Freedom of Learning Program of Indonesia Cyber Education'], 'id_prodi_gerbang' => '88189', 'study_program_type_id' => 1],
            ['id' => 18, 'department_id' => null, 'name' => ['id' => 'Outbound Permata Merdeka', 'en' => 'Outbound Permata Merdeka'], 'id_prodi_gerbang' => '99191', 'study_program_type_id' => 1],
            ['id' => 19, 'department_id' => null, 'name' => ['id' => 'Outbound Permata Sakti', 'en' => 'Permata Sakti Outbound'], 'id_prodi_gerbang' => '99198', 'study_program_type_id' => 1],
            ['id' => 20, 'department_id' => null, 'name' => ['id' => 'Outbound Pertukaran Mahasiswa Merdeka-DN', 'en' => 'Outbound Student Exchange Merdeka-DN'], 'id_prodi_gerbang' => '99194', 'study_program_type_id' => 1],
            ['id' => 21, 'department_id' => null, 'name' => ['id' => 'Outbound PKKM', 'en' => 'Outbound PKKM'], 'id_prodi_gerbang' => '99186', 'study_program_type_id' => 1],
            ['id' => 22, 'department_id' => null, 'name' => ['id' => 'Outbound Wirausaha Mahasiswa Merdeka', 'en' => 'Outbound Independent Student Entrepreneurship'], 'id_prodi_gerbang' => '99185', 'study_program_type_id' => 1],
            ['id' => 23, 'department_id' => 4, 'name' => ['id' => 'Perencanaan Wilayah dan Kota', 'en' => 'Urban and Regional Planning'], 'id_prodi_gerbang' => '44108', 'study_program_type_id' => 1],
            ['id' => 24, 'department_id' => 6, 'name' => ['id' => 'Rekayasa Keselamatan', 'en' => 'Safety Engineering'], 'id_prodi_gerbang' => '33118', 'study_program_type_id' => 1],
            ['id' => 25, 'department_id' => 2, 'name' => ['id' => 'Sistem Informasi', 'en' => 'Information Systems'], 'id_prodi_gerbang' => '11110', 'study_program_type_id' => 1],
            ['id' => 26, 'department_id' => 1, 'name' => ['id' => 'Statistika', 'en' => 'Statistics'], 'id_prodi_gerbang' => '11116', 'study_program_type_id' => 1],
            ['id' => 27, 'department_id' => 2, 'name' => ['id' => 'Teknik Elektro', 'en' => 'Electrical Engineering'], 'id_prodi_gerbang' => '33104', 'study_program_type_id' => 1],
            ['id' => 28, 'department_id' => 5, 'name' => ['id' => 'Teknik Industri', 'en' => 'Industrial Engineering'], 'id_prodi_gerbang' => '33112', 'study_program_type_id' => 1],
            ['id' => 29, 'department_id' => 3, 'name' => ['id' => 'Teknik Kelautan', 'en' => 'Ocean Engineering'], 'id_prodi_gerbang' => '22114', 'study_program_type_id' => 1],
            ['id' => 30, 'department_id' => 6, 'name' => ['id' => 'Teknik Kimia', 'en' => 'Chemical Engineering'], 'id_prodi_gerbang' => '33105', 'study_program_type_id' => 1],
            ['id' => 31, 'department_id' => 3, 'name' => ['id' => 'Teknik Lingkungan', 'en' => 'Environmental Engineering'], 'id_prodi_gerbang' => '55113', 'study_program_type_id' => 1],
            ['id' => 32, 'department_id' => 5, 'name' => ['id' => 'Teknik Logistik', 'en' => 'Logistic Engineering'], 'id_prodi_gerbang' => '33121', 'study_program_type_id' => 1],
            ['id' => 33, 'department_id' => 5, 'name' => ['id' => 'Teknik Material dan Metalurgi', 'en' => 'Materials and Metallurgical Engineering'], 'id_prodi_gerbang' => '55106', 'study_program_type_id' => 1],
            ['id' => 34, 'department_id' => 5, 'name' => ['id' => 'Teknik Mesin', 'en' => 'Mechanical Engineering'], 'id_prodi_gerbang' => '33103', 'study_program_type_id' => 1],
            ['id' => 35, 'department_id' => 3, 'name' => ['id' => 'Teknik Perkapalan', 'en' => 'Naval Architecture'], 'id_prodi_gerbang' => '22109', 'study_program_type_id' => 1],
            ['id' => 36, 'department_id' => 4, 'name' => ['id' => 'Teknik Sipil', 'en' => 'Civil Engineering'], 'id_prodi_gerbang' => '44107', 'study_program_type_id' => 1],
            ['id' => 37, 'department_id' => 6, 'name' => ['id' => 'Teknologi Pangan', 'en' => 'Food Technology'], 'id_prodi_gerbang' => '22119', 'study_program_type_id' => 1],
            ['id' => 38, 'department_id' => null, 'name' => ['id' => 'Transfer Kredit Earning', 'en' => 'Credit Transfer Earning'], 'id_prodi_gerbang' => '99193', 'study_program_type_id' => 1],
            ['id' => 39, 'department_id' => null, 'name' => ['id' => 'Tahap Persiapan Bersama', 'en' => 'Tahap Persiapan Bersama'], 'id_prodi_gerbang' => '00000', 'study_program_type_id' => 2],
            ['id' => 40, 'department_id' => 2, 'name' => ['id' => 'Manajemen Teknologi', 'en' => 'Technology Management'], 'id_prodi_gerbang' => '67201', 'study_program_type_id' => 1],
            ['id' => 41, 'department_id' => 2, 'name' => ['id' => 'Teknik Biomedis', 'en' => 'Biomedical Engineering'], 'id_prodi_gerbang' => '11125', 'study_program_type_id' => 1],
            ['id' => 42, 'department_id' => 3, 'name' => ['id' => 'Teknik Sistem Perkapalan', 'en' => 'Marine Engineering System'], 'id_prodi_gerbang' => '22123', 'study_program_type_id' => 1],
            ['id' => 43, 'department_id' => 3, 'name' => ['id' => 'Teknik Transportasi Laut', 'en' => 'Marine Transportation Engineering'], 'id_prodi_gerbang' => '22124', 'study_program_type_id' => 1],
        ];
    }
}
