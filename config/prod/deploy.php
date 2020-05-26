<?php

use EasyCorp\Bundle\EasyDeployBundle\Configuration\Option;
use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;
use EasyCorp\Bundle\EasyDeployBundle\Requirement\AllowsLoginViaSsh;
use EasyCorp\Bundle\EasyDeployBundle\Requirement\CommandExists;
use EasyCorp\Bundle\EasyDeployBundle\Server\Server;

return new class extends DefaultDeployer
{
    public function getRequirements(): array
    {
        $localhost = $this->getContext()->getLocalHost();
        $allServers = $this->getServers()->findAll();

        $requirements[] = new CommandExists([$localhost], 'git');
        $requirements[] = new CommandExists([$localhost], 'ssh');

        $requirements[] = new AllowsLoginViaSsh($allServers);

        return $requirements;
    }

    public function configure()
    {
        return $this->getConfigBuilder()
            ->server('root@212.227.213.145')
            ->deployDir('/var/www/vhosts/faceit_encounters')
            ->repositoryUrl('git@github.com:TheRealFantasia/faceit_encounters.git')
            ->repositoryBranch('master')
            ->useSshAgentForwarding(true)
        ;
    }

    public function beforePreparing()
    {
        $this->log('<h3>Copying over the .env files</>');
        $this->runRemote('cp {{ deploy_dir }}/env/.env {{ project_dir }}/.env');
    }

    public function beforeFinishingDeploy()
    {
        $this->runRemote('{{ console_bin }} doctrine:migrations:diff')[0]->getOutput();
        $this->runRemote('{{ console_bin }} doctrine:migrations:migrate');
        $this->runRemote('cd {{ project_dir }} && yarn install');
    }
};
