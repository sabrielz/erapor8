<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PesertaDidik;
use App\Models\PraktikKerjaLapangan;
use App\Models\RombonganBelajar;
use App\Models\Semester;

class LeggerPklExport implements FromView, ShouldAutoSize
{
    use Exportable;
    public function query(array $data)
    {
        $this->rombongan_belajar = $data['rombongan_belajar'];
        $this->rombongan_belajar_id = $data['rombongan_belajar_id'];
        $this->sekolah_id = $data['sekolah_id'];
        $this->semester_id = $data['semester_id'];
        
        return $this;
    }
    public function view(): View
    {
        // Get all PKL records for this rombel
        $pkl_list = PraktikKerjaLapangan::where(function($query){
            $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
            $query->where('semester_id', $this->semester_id);
            $query->where('sekolah_id', $this->sekolah_id);
        })->with([
            'akt_pd.dudi',
            'tp_pkl.tp',
            'guru',
        ])->get();

        $pkl_ids = $pkl_list->pluck('pkl_id')->toArray();

        // Collect all unique TPs across all PKLs
        $all_tp = [];
        foreach($pkl_list as $pkl){
            foreach($pkl->tp_pkl as $tp_pkl){
                if($tp_pkl->tp && !isset($all_tp[$tp_pkl->tp->tp_id])){
                    $all_tp[$tp_pkl->tp->tp_id] = $tp_pkl->tp;
                }
            }
        }

        $data_siswa = PesertaDidik::whereHas('anggota_rombel', function($query){
            $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
        })->with([
            'kelas' => function($query){
                $query->where('rombongan_belajar.semester_id', $this->semester_id);
                $query->where('tingkat', '<>', 0);
                $query->where('jenis_rombel', 1);
                $query->with(['jurusan_sp.jurusan.parent']);
            },
            'anggota_rombel' => function($query){
                $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
                $query->with(['absensi']);
            },
            'all_pd_pkl' => function($query) use ($pkl_ids){
                $query->whereIn('pkl_id', $pkl_ids);
                $query->with(['praktik_kerja_lapangan.akt_pd.dudi', 'praktik_kerja_lapangan.guru']);
            },
            'nilai_pkl' => function($query) use ($pkl_ids){
                $query->whereIn('pkl_id', $pkl_ids);
                $query->with(['tp']);
            },
            'all_absensi_pkl' => function($query) use ($pkl_ids){
                $query->whereIn('pkl_id', $pkl_ids);
            },
        ])->orderByRaw('LOWER(nama) ASC')->get();

        $semester = Semester::find($this->semester_id);

        $params = array(
            'data_siswa' => $data_siswa,
            'pkl_list' => $pkl_list,
            'all_tp' => array_values($all_tp),
            'rombongan_belajar' => RombonganBelajar::with(['sekolah'])->find($this->rombongan_belajar_id),
            'tahun_ajaran' => $semester->nama,
        );

        return view('laporan.legger_pkl', $params);
    }
}
