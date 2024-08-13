<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\GuruKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::query();
        $key = $request->get('key');

        if ($request->has('key')) {
            $key = $request->get('key');
            $query->where(function ($q) use ($key) {
                $q->where('nama', 'like', "%{$key}%");
            });
        }
        $guru = $query->orderBy('created_at', 'desc')->paginate(2)->withQueryString();
        return view('guru.index', compact('guru'));
    }

    public function create()
    {
        $mapel = Mapel::all();
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        return view('guru.create', compact('mapel', 'kelas', 'jurusan'));
    }

    public function store(Request $request)
    {
        // dd($request->id_mapel);
        $request->validate([
            'nuptk' => ['required', 'unique:guru,nuptk', 'regex:/^[a-zA-Z0-9]+$/'],
            'nama' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:255',
            'phone' => ['required', 'unique:guru,phone', 'regex:/^[0-9]+$/'],
            'email' => ['required', 'email', 'unique:guru,email'],
            'id_mapel.*' => 'required|exists:tb_mapel,id_mapel',
            'id_kelas' => 'required|exists:tb_kelas,id_kelas',
            'id_jurusan' => 'required|exists:tb_jurusan,id_jurusan',
        ], [
            'nuptk.required' => 'Nuptk wajib diisi.',
            'nuptk.unique' => 'Nuptk sudah terdaftar.',
            'nuptk.regex' => 'Nuptk hanya boleh berisi huruf dan angka.',
            'image.required' => 'Foto wajib diisi.',
            'image.image' => 'File gambar harus berformat jpg,png dan svg.',
            'image.max' => 'Maximal ukuran file 2048 MB.',
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'nama.regex' => 'Nama hanya boleh berisi huruf.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'agama.required' => 'Agama wajib diisi.',
            'agama.string' => 'Agama harus berupa teks.',
            'agama.max' => 'Agama maksimal 255 karakter.',
            'phone.required' => 'No HP wajib diisi.',
            'phone.unique' => 'No HP sudah terdaftar.',
            'phone.regex' => 'No HP harus berupa anggka.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.email' => 'Email tidak valid.',
            'id_mapel.required' => 'Mapel wajib diisi.',
            'id_mapel.exists' => 'Mapel tidak valid.',
            'id_kelas.required' => 'Kelas wajib diisi.',
            'id_kelas.exists' => 'Kelas tidak valid.',
            'id_jurusan.required' => 'Jurusan wajib diisi.',
            'id_jurusan.exists' => 'Jurusan tidak valid.',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Handle image upload and storage
            $imagePath = $request->file('image')->store('images', 'public');
            $data['image'] = $imagePath;
        }

        $guru = Guru::create($data);

        foreach ($request->id_mapel as $mapel_id) {
            GuruMapel::create([
                'guru_id' => $guru->id,
                'mapel_id' => $mapel_id
            ]);
        };

        foreach ($request->id_kelas as $kelas_id) {
            GuruKelas::create([
                'guru_id' => $guru->id,
                'kelas_id' => $kelas_id
            ]);
        };




        return redirect()->route('guru.index')->with('status', 'Data guru berhasil disimpan!');
    }

    public function edit($id)
    {
        $guru = Guru::findOrFail($id);
        $mapel = Mapel::all();
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        return view('guru.edit', compact('guru', 'mapel', 'kelas', 'jurusan'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'nuptk' => ['required', 'unique:guru,nuptk,' . $id, 'regex:/^[a-zA-Z0-9]+$/'],
        'nama' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        'agama' => 'required|string|max:255',
        'phone' => ['required', 'unique:guru,phone,' . $id, 'regex:/^[0-9]+$/'],
        'email' => ['required', 'email', 'unique:guru,email,'  . $id],
        'id_mapel' => 'required|exists:tb_mapel,id_mapel',
        'id_kelas' => 'required|exists:tb_kelas,id_kelas',
        'id_jurusan' => 'required|exists:tb_jurusan,id_jurusan',
    ], [
        'nuptk.required' => 'Nuptk wajib diisi.',
        'nuptk.unique' => 'Nuptk sudah terdaftar.',
        'nuptk.regex' => 'Nuptk hanya boleh berisi huruf dan angka.',
        'nama.required' => 'Nama wajib diisi.',
        'image.image' => 'File foto harus berformat jpg,png dan svg.',
        'image.max' => 'Maximal ukuran foto 2048 MB.',
        'nama.string' => 'Nama harus berupa teks.',
        'nama.max' => 'Nama maksimal 255 karakter.',
        'nama.regex' => 'Nama hanya boleh berisi huruf.',
        'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
        'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        'agama.required' => 'Agama wajib diisi.',
        'agama.string' => 'Agama harus berupa teks.',
        'agama.max' => 'Agama maksimal 255 karakter.',
        'phone.required' => 'No HP wajib diisi.',
        'phone.unique' => 'No HP sudah terdaftar.',
        'phone.regex' => 'No HP harus berupa anggka.',
        'email.required' => 'Email wajib diisi.',
        'email.unique' => 'Email sudah terdaftar.',
        'email.email' => 'Email tidak valid.',
        'id_mapel.required' => 'Mapel wajib diisi.',
        'id_mapel.exists' => 'Mapel tidak valid.',
        'id_kelas.required' => 'Kelas wajib diisi.',
        'id_kelas.exists' => 'Kelas tidak valid.',
        'id_jurusan.required' => 'Jurusan wajib diisi.',
        'id_jurusan.exists' => 'Jurusan tidak valid.',
    ]);

    $guru = Guru::findOrFail($id);
    $data = $request->all();

    if ($request->hasFile('image')) {
        if ($guru->image) {
            Storage::delete('public/' . $guru->image);
        }
        $imagePath = $request->file('image')->store('images', 'public');
        $data['image'] = $imagePath;
    }

    $guru->update($data);

    // Update Mapel associations
    $guru->mapel()->sync($request->id_mapel);

    // Update Kelas associations
    $guru->kelas()->sync($request->id_kelas);

    return redirect()->route('guru.index')->with('status', 'Data guru berhasil diupdate!');
}

    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        if ($guru->image) {
            Storage::delete('public/' . $guru->image);
        }
        $guru->mapel()->detach();
        $guru->kelas()->detach();
        $guru->delete();

        return redirect()->route('guru.index')->with('status', 'Data guru berhasil dihapus!');
}
}