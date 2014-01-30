<?php

namespace Fias\Tests;

use Fias\Action\Validation;

class ActionValidationTest extends Action
{
    public function testNotFound()
    {
        $validate = new Validation($this->db, 'Непонятный адрес');
        $result   = $validate->run();

        $this->isNull($result['id']);
        $this->isFalse($result['is_complete']);
    }

    public function testIncomplete()
    {
        $validate = new Validation($this->db, 'г москва, ул стахановская');
        $result   = $validate->run();

        $this->isFalse($result['is_complete']);
        $this->assertEquals('77303f7c-452b-4e73-b2b0-cbc59fe636c2', $result['id']);
    }

    public function testValid()
    {
        $validate = new Validation($this->db, 'г москва, ул стахановская, 16с17');
        $result   = $validate->run();

        $this->isFalse($result['is_complete']);
        $this->assertEquals('841254dc-0074-41fe-99ba-0c8501526c04', $result['id']);
    }

    public function testZeroLevel()
    {
        $validate = new Validation($this->db, 'г москва');
        $result   = $validate->run();

        $this->isFalse($result['is_complete']);
        $this->assertEquals('29251dcf-00a1-4e34-98d4-5c47484a36d4', $result['id']);
    }
}
