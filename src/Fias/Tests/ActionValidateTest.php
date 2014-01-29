<?php

namespace Fias\Tests;

use Fias\Action\Validate;

class ActionValidateTest extends Action
{
    public function testNotFound()
    {
        $validate = new Validate('Непонятный адрес', $this->db);
        $result   = $validate->run();

        $this->isNull($result['id']);
        $this->isFalse($result['is_valid']);
        $this->isFalse($result['is_complete']);
    }

    public function testIncomplete()
    {
        $validate = new Validate('г москва, ул стахановская', $this->db);
        $result   = $validate->run();

        $this->isTrue($result['is_valid']);
        $this->isFalse($result['is_complete']);
        $this->assertEquals('77303f7c-452b-4e73-b2b0-cbc59fe636c2', $result['id']);
    }

    public function testValid()
    {
        $validate = new Validate('г москва, ул стахановская, 16с17', $this->db);
        $result   = $validate->run();

        $this->isTrue($result['is_valid']);
        $this->isFalse($result['is_complete']);
        $this->assertEquals('841254dc-0074-41fe-99ba-0c8501526c04', $result['id']);
    }

    public function testZeroLevel()
    {
        $validate = new Validate('г москва', $this->db);
        $result   = $validate->run();

        $this->isTrue($result['is_valid']);
        $this->isFalse($result['is_complete']);
        $this->assertEquals('29251dcf-00a1-4e34-98d4-5c47484a36d4', $result['id']);
    }
}
