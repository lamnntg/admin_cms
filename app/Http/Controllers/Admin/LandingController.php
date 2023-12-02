<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageConfig;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LandingController extends Controller
{
    public function index()
    {
        $landingConfigs = LandingPageConfig::all();

        return view('landing', compact('landingConfigs'));
    }

    public function changeBanner(Request $request)
    {
        DB::beginTransaction();

        try {
            $params = $request->all();

            if (!isset($params['file'])) {
                return redirect()->back();
            }
            $params['url'] = Cloudinary::upload($request->file('file')->getRealPath())->getSecurePath();

            switch ($params['banner'] ?? null) {
                case 1:
                    $updateValues['key'] = 'banner_1';
                    // $params['url'] = Storage::disk('local')->put('example.txt', 'Contents');

                    // $params['url'] = asset($request->file('file')->storeAs('public/banner', 'banner_1.png'));

                    break;
                case 2:
                    $updateValues['key'] = 'banner_2';
                    // $params['url'] = $request->file('file')->storeAs('public/banner', 'banner_2');

                    break;
                default:
                    $updateValues['key'] = 'banner_3';
                    // $params['url'] = $request->file('file')->storeAs('public/banner', 'banner_3');

                    break;
            }
            $updateValues['value'] = $params['url'];
            LandingPageConfig::where('key', $updateValues['key'])->first()->update([
                'value' => $updateValues['value'],
            ]);

            DB::commit();
            // all good

        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
        }

        return redirect()->route('landing');
    }
}
