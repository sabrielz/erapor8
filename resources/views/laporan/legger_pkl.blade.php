<table class="table" border="1">
    <tr>
        <td colspan="2">Sekolah</td>
        <td colspan="2">{{ $rombongan_belajar->sekolah->nama }}</td>
    </tr>
    <tr>
        <td colspan="2">NPSN</td>
        <td colspan="2">{{ $rombongan_belajar->sekolah->npsn }}</td>
    </tr>
    <tr>
        <td colspan="2">Kelas</td>
        <td colspan="2">{{ $rombongan_belajar->nama }}</td>
    </tr>
    <tr>
        <td colspan="2">Tahun Pelajaran</td>
        <td colspan="2">{{ $tahun_ajaran }}</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="2"></td>
    </tr>
</table>
<table class="table" border="1">
    <thead>
        <tr>
            <th rowspan="3" align="center" valign="middle">No</th>
            <th rowspan="3" align="center" valign="middle">NAMA PESERTA DIDIK</th>
            <th rowspan="3" align="center" valign="middle">NISN</th>
            <th rowspan="3" align="center" valign="middle">PROGRAM KEAHLIAN</th>
            <th rowspan="3" align="center" valign="middle">KONSENTRASI KEAHLIAN</th>
            <th rowspan="3" align="center" valign="middle">MITRA DUDI</th>
            <th rowspan="3" align="center" valign="middle">TANGGAL MULAI</th>
            <th rowspan="3" align="center" valign="middle">TANGGAL SELESAI</th>
            <th rowspan="3" align="center" valign="middle">INSTRUKTUR</th>
            <th rowspan="3" align="center" valign="middle">PEMBIMBING</th>
            @if(count($all_tp) > 0)
                <th colspan="{{ count($all_tp) * 2 }}" align="center">NILAI TUJUAN PEMBELAJARAN</th>
            @endif
            <th rowspan="3" align="center" valign="middle">CATATAN</th>
            <th colspan="3" align="center">KEHADIRAN PKL</th>
        </tr>
        <tr>
            @foreach ($all_tp as $tp)
                <th colspan="2" align="center">{{ $tp->deskripsi }}</th>
            @endforeach
            <th rowspan="2" align="center" valign="middle">S</th>
            <th rowspan="2" align="center" valign="middle">I</th>
            <th rowspan="2" align="center" valign="middle">A</th>
        </tr>
        <tr>
            @foreach ($all_tp as $tp)
                <th align="center">Skor</th>
                <th align="center">Deskripsi</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data_siswa as $siswa)
            <tr>
                <td align="center" valign="top">{{ $loop->iteration }}</td>
                <td valign="top">{{ $siswa->nama }}</td>
                <td align="center" valign="top">{{ $siswa->nisn }}</td>
                <td valign="top">{{ $siswa->kelas->jurusan_sp->jurusan->parent->nama_jurusan ?? '-' }}</td>
                <td valign="top">{{ $siswa->kelas->jurusan_sp->nama_jurusan_sp ?? '-' }}</td>
                
                <?php
                $dudi_list = [];
                $tgl_mulai_list = [];
                $tgl_selesai_list = [];
                $instruktur_list = [];
                $pembimbing_list = [];
                $catatan_list = [];
                
                foreach($siswa->all_pd_pkl as $pd_pkl){
                    $pkl = $pd_pkl->praktik_kerja_lapangan;
                    if($pkl){
                        if($pkl->akt_pd && $pkl->akt_pd->dudi){
                            $dudi_list[] = $pkl->akt_pd->dudi->nama_dudi;
                        }
                        $tgl_mulai_list[] = \Carbon\Carbon::parse($pkl->tanggal_mulai)->translatedFormat('d F Y');
                        $tgl_selesai_list[] = \Carbon\Carbon::parse($pkl->tanggal_selesai)->translatedFormat('d F Y');
                        $instruktur_list[] = $pkl->instruktur;
                        if($pkl->guru){
                            $pembimbing_list[] = $pkl->guru->nama_lengkap;
                        }
                    }
                    if($pd_pkl->catatan){
                        $catatan_list[] = $pd_pkl->catatan;
                    }
                }
                
                $sakit = 0;
                $izin = 0;
                $alpa = 0;
                $has_absensi = false;
                foreach($siswa->all_absensi_pkl as $absensi){
                    $sakit += $absensi->sakit ?? 0;
                    $izin += $absensi->izin ?? 0;
                    $alpa += $absensi->alpa ?? 0;
                    $has_absensi = true;
                }
                ?>
                <td valign="top">{{ !empty($dudi_list) ? implode(', ', array_unique($dudi_list)) : '-' }}</td>
                <td valign="top">{{ !empty($tgl_mulai_list) ? implode(', ', array_unique($tgl_mulai_list)) : '-' }}</td>
                <td valign="top">{{ !empty($tgl_selesai_list) ? implode(', ', array_unique($tgl_selesai_list)) : '-' }}</td>
                <td valign="top">{{ !empty($instruktur_list) ? implode(', ', array_unique($instruktur_list)) : '-' }}</td>
                <td valign="top">{{ !empty($pembimbing_list) ? implode(', ', array_unique($pembimbing_list)) : '-' }}</td>
                
                @foreach ($all_tp as $tp)
                    <?php
                    $nilai = '-';
                    $deskripsi_nilai = '-';
                    foreach($siswa->nilai_pkl as $nilai_pkl){
                        if($nilai_pkl->tp_id == $tp->tp_id){
                            $nilai = $nilai_pkl->nilai;
                            $deskripsi_nilai = $nilai_pkl->deskripsi;
                            break;
                        }
                    }
                    ?>
                    <td align="center" valign="top">{{ $nilai }}</td>
                    <td valign="top">{{ $deskripsi_nilai }}</td>
                @endforeach
                
                <td valign="top">{{ !empty($catatan_list) ? implode("\n", array_unique($catatan_list)) : '-' }}</td>
                
                <td align="center" valign="top">{{ $has_absensi && $sakit > 0 ? $sakit : '-' }}</td>
                <td align="center" valign="top">{{ $has_absensi && $izin > 0 ? $izin : '-' }}</td>
                <td align="center" valign="top">{{ $has_absensi && $alpa > 0 ? $alpa : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
