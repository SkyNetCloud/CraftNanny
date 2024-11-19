


-- Define the module files

local modules = {
    "energy.lua",
    "fluid.lua",
    "redstone.lua",
    "reactor.lua"
}

-- Function to check if any module file exists
local function checkModules()
    for _, module in ipairs(modules) do
        if fs.exists(module) then
            return module
        end
    end
    return nil
end

-- Function to run the corresponding module code
local function runModule(module)
    if module == "player.lua" then
        -- Run player module code
        shell.run("player.lua")
    elseif module == "energy.lua" then
        -- Run energy module code
        shell.run("energy.lua")
    elseif module == "fluid.lua" then
        -- Run fluid module code
        shell.run("fluid.lua")
    elseif module == "redstone.lua" then
        -- Run redstone module code
        shell.run("redstone.lua")
    elseif module == "reactor.lua" then
        shell.run("reactor.lua")
    end
end

-- Main function
local function main()
    local module = checkModules()
    if module then
        print("Module found:", module)
        runModule(module)
    else
        print("No module found.")
    end
end

-- Run the main function
main()