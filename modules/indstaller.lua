local githubRepo = "SkyNetCloud/CraftNanny"
local branch = "main"
local files = {
    installer = "installer.lua",
    player_module = "player_module.lua",
    redstone_module = "redstone_module.lua",
    fluid_module = "fluid_module.lua",
    energy_module = "energy_module.lua",
    hash_api = "hash_api.lua",
    startup = "startup.lua"
}

local function downloadFromGitHub(file)
    local url = "https://raw.githubusercontent.com/" .. githubRepo .. "/" .. branch .. "/" .. file
    local localPath = fs.combine(shell.dir(), file)
    local response = http.get(url)
    if response then
        local content = response.readAll()
        response.close()
        local file = fs.open(localPath, "w")
        file.write(content)
        file.close()
        return true
    else
        print("Failed to download file: " .. file)
        return false
    end
end

local function downloadFiles()
    for _, file in pairs(files) do
        if not downloadFromGitHub(file) then
            return false
        end
    end
    return true
end

local function setup()
    term.clear()
    term.setTextColor(colors.white)
    term.setBackgroundColor(colors.black)
    term.setCursorPos(1, 1)
    print("CraftNanny Module Installer")
    print("CraftNanny.org")
end

local function install()
    if not downloadFiles() then
        print("Installation failed.")
        return
    end

    -- Your existing login and installation logic goes here
end

local function main()
    setup()
    print("Downloading files from GitHub...")
    install()
end

main()
