<?php

namespace App\Enums;

enum RolePengguna: string
{
    case Admin = 'Admin';
    case Dosen = 'Dosen';
    case Mahasiswa = 'Mahasiswa';
}
