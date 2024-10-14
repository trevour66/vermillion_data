<?php

namespace App\Http\Controllers;

use App\Models\Company_Database;
use Error;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $website_data = Company_Database::latest()->paginate(20);

        return Inertia::render('Dashboard', [
            "data" => $website_data
        ]);
    }

    public function index_api()
    {
        $website_data = Company_Database::latest()->paginate(30);
        return Inertia::render('Dashboard');
    }

    public function add_websites_api(Request $request)
    {
        try {
            $validated = $request->validate([
                'websites' => 'required|array'
            ]);

            $properly_formatted_domains = [];
            $improperly_formatted_domains = [];

            foreach ($validated['websites'] as $key => $value) {
                logger($value);

                if ($this->validateDomain($value)) {
                    $formatted_website_name = "www." . $value;

                    logger('accepted');
                    Company_Database::updateOrCreate(
                        ['website' => $formatted_website_name],
                        ['website' => $formatted_website_name]
                    );

                    array_push($properly_formatted_domains, [$value]);
                } else {
                    logger('not accepted');
                    array_push($improperly_formatted_domains, $value);
                }
            }

            $this->sendToSheet($properly_formatted_domains);

            return response()->json([
                'status' => 'success',
                'improperly_formatted_domains' => $improperly_formatted_domains
            ]);
        } catch (\Throwable $th) {
            logger($th->getMessage());
            return response()->json([
                'status' => 'error',
            ]);
        }
    }

    private function validateDomain($domain)
    {
        $isDomainValid = (bool) preg_match('/^(?!https?:\/\/)(?!www\.)(?!\-)(?:[a-zA-Z0-9\-]{1,63}\.)+(?:[a-zA-Z]{2,63})$/', $domain);

        if ($isDomainValid) {
            return true;
        } else {
            return false;
        }
    }

    private function sendToSheet($data)
    {
        try {
            $GOOGLE_APP_SCRIPT_URL =  env('APP_NAME', null);

            if ($GOOGLE_APP_SCRIPT_URL == null) {
                throw new Error('GOOGLE_APP_SCRIPT_URL not in .env');
            }

            $request = Http::post($GOOGLE_APP_SCRIPT_URL, [
                "websites" => $data
            ]);

            logger($request->body());
        } catch (\Throwable $th) {
            logger($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company_Database $website_id)
    {
        $website_id->delete();
        return back();
    }
}
