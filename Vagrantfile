# -*- mode: ruby -*-
# vi: set ft=ruby :
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.ssh.shell = "bash -c 'BASH_ENV=/home/vagrant/.bashrc exec bash'"
    config.vm.box = "puppetlabs/ubuntu-14.04-64-puppet"
    config.vm.hostname = "ilios.dev"
    config.vm.network :private_network, ip: "10.10.10.10"
    config.vm.network "forwarded_port", guest: 443, host: 8443, host_ip: "127.0.0.1"

    if Vagrant::Util::Platform.windows?
        config.vm.synced_folder ".", "/vagrant", id: "ilios-root"
    else
        config.vm.synced_folder ".", "/vagrant", :nfs => { :mount_options => ["dmode=777","fmode=777"], :nfs_version => "3" }, id: "ilios-root"
    end

    config.vm.provider "virtualbox" do |vb|
        vb.customize ["modifyvm", :id, "--memory", "3072"]
        vb.name = "ilios3.dev"
    end

    config.vm.provider "vmware_workstation" do |vw, override|
        override.vm.box_url = "https://atlas.hashicorp.com/puppetlabs/boxes/ubuntu-14.04-64-puppet/versions/1.0.0/providers/vmware_desktop.box"
        vw.vmx["memsize"] = "3072"
        vw.vmx["displayname"] = "ilios3.dev"
    end

    config.vm.provision "shell" do |shell|
        shell.path = "provision/shell/init.sh"
        shell.binary = true
    end

    if Vagrant.has_plugin?("vagrant-cachier")
        config.cache.scope = :box
        unless Vagrant::Util::Platform.windows?
            config.cache.synced_folder_opts = {
                type: :nfs
            }
        end
    end
end
