<?php

class LockRegisterService
{
    public static function checkLocked($object, $modelClass, $listClass = NULL)
    {
        $usuario = SystemUser::find(TSession::getValue('userid'));
        $lock_time = ParametroSistema::getParametroValue('lock_time');
        $lock_time = $lock_time ?? 5;
        if (!empty($object->usuario_lock_id) && $object->usuario_lock_id != $usuario->id
            && date('Y-m-d H:i:s', strtotime($object->data_lock)) > date('Y-m-d H:i:s', strtotime("-{$lock_time} minutes")))
        {
            if (isset($listClass) && !empty($listClass))
            {
                TApplication::loadPage($listClass, 'onShow');
            }
            throw new Exception('O registro encontra-se bloqueado para o usuário:<br/><b>'.$object->usuario_lock->nome.'</b>.');
        }
        else
        {
            self::clearLock($usuario->id, $modelClass);
            $object->usuario_lock_id = $usuario->id;
            $object->data_lock = date('Y-m-d H:i:s');
            $object->store();
        }
    }
    
    private static function clearLock($usuarioId, $modelClass)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('usuario_lock_id', '=', $usuarioId));
        $repository = new TRepository($modelClass);
        $repository->update(['usuario_lock_id' => NULL, 'data_lock' => NULL], $criteria);
    }
}
