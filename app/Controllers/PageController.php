<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\{Database as DB, Occurrence, Indicators};

final class PageController extends Controller {
    public function home(): void { $this->view('home',['title'=>'Sua cidade, nossa responsabilidade','stats'=>Indicators::public(),'recent'=>Occurrence::listing([])['rows'],'notices'=>$this->notices()]+Occurrence::taxonomy()); }
    private function notices(): array { return DB::all("SELECT * FROM avisos WHERE status='PUBLICADO' AND (expira_em IS NULL OR expira_em>NOW()) ORDER BY publicado_em DESC LIMIT 30"); }
    public function indicators(): void { $this->view('indicators',['title'=>'Um olhar sobre a cidade','stats'=>Indicators::public()]); }
    public function indicatorsApi(): void { $this->json(Indicators::public()); }
    public function noticesPage(): void { $this->view('notices',['title'=>'Avisos à comunidade','notices'=>$this->notices()]); }
    public function about(): void { $this->view('about',['title'=>'Participação que aproxima']); }
    public function privacy(): void { $this->view('privacy',['title'=>'Termos de uso e privacidade']); }
}
