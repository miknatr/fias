<?php
// STOPPER рефакторинг, подуамать над форматом (передача is_complete в complete? Нафига нужен is_valid если есть id?)
// STOPPER наименование команд
// STOPPER стиль кодирования
// STOPPER JMeter
// STOPPER home_id в таблице houses? ORLY?
// STOPPER чего похожие действия такие разные? И вообще дублирование кода может быть?
// STOPPER "AS" в SQL запросах запрещены по богомерзкому SQL стилю кодирования. Убрать.
// STOPPER сделать метод fetchEl
// STOPPER LIMIT
// STOPPER пересмотреть индексы в связи с level и использованием title для complete
// STOPPER формирование JSON ответа
namespace Fias\Tests;

use Fias\Action\Complete;

class ActionCompleteTest extends Action
{
    public function testNotFound()
    {
        $complete = new Complete('Нави, Главная б', null, $this->db);
        $result   = $complete->run();

        $this->assertEquals(0, $result['count']);
    }

    public function testAddressCompletion()
    {
        $complete = new Complete('г Москва, Ста', '29251dcf-00a1-4e34-98d4-5c47484a36d4', $this->db);
        $result   = $complete->run();

        $this->assertEquals(4, $result['count']);
        $this->assertEquals('пр Ставропольский', $result['rows'][0]['title']);
    }

    public function testHomeCompletion()
    {
        $complete = new Complete('г Москва, Стахановская, 1', '77303f7c-452b-4e73-b2b0-cbc59fe636c2', $this->db);
        $result   = $complete->run();

        $this->assertEquals(3, $result['count']);
        $this->assertEquals('1к1', $result['rows'][0]['title']);
    }
}
